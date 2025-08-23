<?php
require_once 'config.php';
init_db();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get posts
$pdo = get_db();
$stmt = $pdo->query("SELECT p.content, p.created_at, u.username 
                     FROM posts p JOIN users u ON p.user_id = u.id 
                     ORDER BY p.created_at DESC");
$posts = $stmt->fetchAll();

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
    $stmt = $pdo->prepare("INSERT INTO posts (user_id, content) VALUES (?, ?)");
    $stmt->execute([$_SESSION['user_id'], $_POST['content']]);
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
            <form method="POST">
                <div class="form-group">
                    <textarea name="content" placeholder="What's on your mind?" rows="3" required></textarea>
                </div>
                <button type="submit">Post</button>
            </form>
        </div>

        <?php if ($posts): ?>
            <?php foreach ($posts as $post): ?>
            <div class="post">
                <p><strong><?= htmlspecialchars($post['username']) ?></strong></p>
                <p><?= htmlspecialchars($post['content']) ?></p>
                <small><?= $post['created_at'] ?></small>
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