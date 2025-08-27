<?php
require_once 'config.php';
init_db();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get posts
$pdo = get_db();
try {
    $stmt = $pdo->query("SELECT p.content, p.created_at, u.username, p.file_path, p.file_type
                         FROM posts p JOIN users u ON p.user_id = u.id 
                         ORDER BY p.created_at DESC");
    $posts = $stmt->fetchAll();
} catch (Exception $e) {
    // Fallback for old database without file columns
    $stmt = $pdo->query("SELECT p.content, p.created_at, u.username, NULL as file_path, NULL as file_type
                         FROM posts p JOIN users u ON p.user_id = u.id 
                         ORDER BY p.created_at DESC");
    $posts = $stmt->fetchAll();
}

// Get friend requests
$stmt = $pdo->prepare("SELECT f.id, u.username FROM friends f JOIN users u ON f.user_id = u.id WHERE f.friend_id = ? AND f.status = 'pending'");
$stmt->execute([$_SESSION['user_id']]);
$friend_requests = $stmt->fetchAll();

// Handle friend request actions
if ($_GET['accept'] ?? false) {
    $stmt = $pdo->prepare("UPDATE friends SET status = 'accepted' WHERE id = ?");
    $stmt->execute([$_GET['accept']]);
    header('Location: index.php');
    exit;
}
if ($_GET['decline'] ?? false) {
    $stmt = $pdo->prepare("DELETE FROM friends WHERE id = ?");
    $stmt->execute([$_GET['decline']]);
    header('Location: index.php');
    exit;
}

// Handle new post
if ($_POST['content'] ?? false) {
    $file_path = null;
    $file_type = null;
    
    // Handle file upload
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $allowed = ['mp4', 'mp3', 'png', 'jpg', 'jpeg'];
        $filename = $_FILES['file']['name'];
        $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($file_ext, $allowed)) {
            // Ensure uploads directory exists
            if (!is_dir('uploads')) {
                mkdir('uploads', 0755, true);
            }
            
            $new_filename = uniqid() . '.' . $file_ext;
            $upload_path = 'uploads/' . $new_filename;
            
            if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_path)) {
                $file_path = $upload_path;
                $file_type = $file_ext;
            }
        }
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO posts (user_id, content, file_path, file_type) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $_POST['content'], $file_path, $file_type]);
    } catch (Exception $e) {
        // Fallback for old database without file columns
        $stmt = $pdo->prepare("INSERT INTO posts (user_id, content) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $_POST['content']]);
    }
    
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Feed - Private Social</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1877f2; color: white; padding: 15px; text-align: center; }
        .nav { background: white; padding: 10px; margin-bottom: 20px; border-radius: 8px; }
        .nav a { color: #1877f2; text-decoration: none; margin-right: 15px; }
        .post { background: white; padding: 15px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .form-group { margin: 15px 0; }
        textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        button { background: #1877f2; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        .notification-btn { background: #4caf50; margin-left: 10px; }
        .notification-btn.disabled { background: #ccc; }
    </style>
    <script>
        let lastPostCount = 0;
        
        function requestNotificationPermission() {
            if ('Notification' in window) {
                Notification.requestPermission().then(function(permission) {
                    if (permission === 'granted') {
                        document.getElementById('notif-btn').textContent = 'Notifications On';
                        checkForNewPosts();
                    }
                });
            }
        }
        
        function checkForNewPosts() {
            if (Notification.permission === 'granted') {
                setInterval(function() {
                    fetch('check_posts.php')
                        .then(response => response.json())
                        .then(data => {
                            if (lastPostCount > 0 && data.count > lastPostCount) {
                                new Notification('New Post!', {
                                    body: data.latest_post,
                                    icon: 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%231877f2"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>'
                                });
                            }
                            lastPostCount = data.count;
                        });
                }, 5000);
            }
        }
        
        window.onload = function() {
            if (Notification.permission === 'granted') {
                document.getElementById('notif-btn').textContent = 'Notifications On';
                checkForNewPosts();
            }
        }
    </script>
</head>
<body>
    <div class="nav">
        <a href="index.php">Home</a>
        <a href="users.php">Find Friends</a>
        <a href="friends.php">My Friends</a>
        <a href="messages.php">Messages</a>
        <?php
        $pdo_nav = get_db();
        $stmt_nav = $pdo_nav->prepare("SELECT username FROM users WHERE id = ?");
        $stmt_nav->execute([$_SESSION['user_id']]);
        $user_nav = $stmt_nav->fetch();
        if ($user_nav && $user_nav['username'] === 'OSRG'):
        ?>
        <a href="admin.php" style="color: #d32f2f; font-weight: bold;">Admin Panel</a>
        <?php endif; ?>
        <a href="logout.php">Logout</a>
    </div>
    
    <div class="container">
        <div class="header">
            <h1>Your Private Feed</h1>
            <button id="notif-btn" class="notification-btn" onclick="requestNotificationPermission()">Enable Notifications</button>
        </div>

        <?php if ($friend_requests): ?>
        <div class="post" style="background: #e3f2fd; border-left: 4px solid #1877f2;">
            <h3>Friend Requests</h3>
            <?php foreach ($friend_requests as $request): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; border-bottom: 1px solid #ddd;">
                <span><strong><?= htmlspecialchars($request['username']) ?></strong> wants to be your friend</span>
                <div>
                    <a href="?accept=<?= $request['id'] ?>" style="background: #42a5f5; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; margin-right: 5px;">Accept</a>
                    <a href="?decline=<?= $request['id'] ?>" style="background: #f44336; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px;">Decline</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="post">
            <h3>Share something...</h3>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <textarea name="content" placeholder="What's on your mind?" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <input type="file" name="file" accept=".mp4,.mp3,.png,.jpg,.jpeg" style="margin-bottom: 10px;">
                    <small style="color: #666;">Upload: MP4, MP3, PNG, JPG (optional)</small>
                </div>
                <button type="submit">Post</button>
            </form>
        </div>

        <?php if ($posts): ?>
            <?php foreach ($posts as $post): ?>
            <div class="post">
                <p><strong><?= htmlspecialchars($post['username']) ?></strong></p>
                <p><?= htmlspecialchars($post['content']) ?></p>
                
                <?php if ($post['file_path']): ?>
                <div style="margin: 10px 0;">
                    <?php if ($post['file_type'] == 'mp4'): ?>
                        <video controls style="width: 100%; max-width: 100%; display: block;">
                            <source src="<?= $post['file_path'] ?>" type="video/mp4">
                        </video>
                    <?php elseif ($post['file_type'] == 'mp3'): ?>
                        <audio controls style="width: 100%; display: block;">
                            <source src="<?= $post['file_path'] ?>" type="audio/mp3">
                        </audio>
                    <?php elseif (in_array($post['file_type'], ['png', 'jpg', 'jpeg'])): ?>
                        <img src="<?= htmlspecialchars($post['file_path']) ?>" alt="Uploaded image" style="width: 100%; max-width: 100%; display: block; border-radius: 8px;">
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <div style="clear: both; margin-top: 10px;">
                    <small><?= date('M j, H:i', strtotime($post['created_at'] . ' +1 hour')) ?></small>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="post">
                <p><strong>Welcome!</strong></p>
                <p>Start by creating your first post above!</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>