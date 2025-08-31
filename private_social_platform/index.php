<?php
require_once 'config.php';
init_db();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get posts with reactions and comments
$pdo = get_db();
try {
    $stmt = $pdo->prepare("
        SELECT p.id, p.content, p.created_at, u.username, u.avatar, p.file_path, p.file_type,
               COUNT(DISTINCT r.id) as reaction_count,
               COUNT(DISTINCT c.id) as comment_count,
               ur.reaction_type as user_reaction
        FROM posts p 
        JOIN users u ON p.user_id = u.id
        LEFT JOIN reactions r ON p.id = r.post_id
        LEFT JOIN comments c ON p.id = c.post_id
        LEFT JOIN reactions ur ON p.id = ur.post_id AND ur.user_id = ?
        WHERE u.approved = 1
        GROUP BY p.id
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $posts = $stmt->fetchAll();
} catch (Exception $e) {
    // Fallback for old database
    $stmt = $pdo->query("SELECT p.id, p.content, p.created_at, u.username, u.avatar, NULL as file_path, NULL as file_type,
                         0 as reaction_count, 0 as comment_count, NULL as user_reaction
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

// Handle reactions
if ($_POST['reaction'] ?? false) {
    $post_id = $_POST['post_id'];
    $reaction = $_POST['reaction'];
    
    // Remove existing reaction or add new one
    $stmt = $pdo->prepare("DELETE FROM reactions WHERE post_id = ? AND user_id = ?");
    $stmt->execute([$post_id, $_SESSION['user_id']]);
    
    if ($reaction !== 'remove') {
        $stmt = $pdo->prepare("INSERT INTO reactions (post_id, user_id, reaction_type) VALUES (?, ?, ?)");
        $stmt->execute([$post_id, $_SESSION['user_id'], $reaction]);
    }
    
    header('Location: index.php');
    exit;
}

// Handle comments
if ($_POST['comment'] ?? false) {
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$_POST['post_id'], $_SESSION['user_id'], $_POST['comment']]);
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
        $file_size = $_FILES['file']['size'];
        $max_size = 10 * 1024 * 1024; // 10MB limit
        
        if ($file_size > $max_size) {
            // File too large - skip upload
        } elseif (in_array($file_ext, $allowed)) {
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
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <title>Feed - OSRG Connect</title>
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
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
    </style>
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script>
        let lastPostCount = 0;
        let quill;
        
        // Initialize WYSIWYG editor
        document.addEventListener('DOMContentLoaded', function() {
            quill = new Quill('#editor', {
                theme: 'snow',
                placeholder: "What's on your mind?",
                modules: {
                    toolbar: [
                        ['bold', 'italic'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }]
                    ]
                }
            });
            
            // Update hidden textarea when form is submitted
            document.getElementById('postForm').addEventListener('submit', function(e) {
                // Always set the content first
                document.getElementById('content').value = quill.root.innerHTML;
                
                const content = quill.getText().trim();
                if (content.length === 0) {
                    e.preventDefault();
                    alert('Please write something before posting!');
                    return false;
                }
                return true;
            });
        });
        
        let notificationsEnabled = false;
        let notificationInterval = null;
        
        function toggleNotifications() {
            if (notificationsEnabled) {
                // Disable notifications
                notificationsEnabled = false;
                localStorage.setItem('notificationsEnabled', 'false');
                if (notificationInterval) {
                    clearInterval(notificationInterval);
                    notificationInterval = null;
                }
                document.getElementById('notif-btn').textContent = 'Enable Notifications';
                document.getElementById('notif-btn').style.background = '#666';
            } else {
                // Enable notifications
                if ('Notification' in window) {
                    if (Notification.permission === 'granted') {
                        // Already granted - enable native notifications
                        notificationsEnabled = true;
                        localStorage.setItem('notificationsEnabled', 'true');
                        document.getElementById('notif-btn').textContent = 'Disable Notifications';
                        document.getElementById('notif-btn').style.background = '#4caf50';
                        checkForNewPosts();
                    } else if (Notification.permission === 'default') {
                        // Request permission
                        Notification.requestPermission().then(function(permission) {
                            if (permission === 'granted') {
                                notificationsEnabled = true;
                                localStorage.setItem('notificationsEnabled', 'true');
                                document.getElementById('notif-btn').textContent = 'Disable Notifications';
                                document.getElementById('notif-btn').style.background = '#4caf50';
                                checkForNewPosts();
                            } else {
                                // User denied - fallback to visual alerts
                                notificationsEnabled = true;
                                localStorage.setItem('notificationsEnabled', 'true');
                                document.getElementById('notif-btn').textContent = 'Disable Notifications';
                                document.getElementById('notif-btn').style.background = '#ff9800';
                                checkForNewPosts();
                            }
                        });
                    } else {
                        // Permission denied - use visual alerts
                        notificationsEnabled = true;
                        localStorage.setItem('notificationsEnabled', 'true');
                        document.getElementById('notif-btn').textContent = 'Disable Notifications';
                        document.getElementById('notif-btn').style.background = '#ff9800';
                        checkForNewPosts();
                    }
                } else {
                    // Browser doesn't support notifications
                    notificationsEnabled = true;
                    localStorage.setItem('notificationsEnabled', 'true');
                    document.getElementById('notif-btn').textContent = 'Disable Notifications';
                    document.getElementById('notif-btn').style.background = '#ff9800';
                    checkForNewPosts();
                }
            }
        }
        
        function showVisualAlert(message) {
            // Create visual notification for iOS/unsupported browsers
            const alert = document.createElement('div');
            alert.style.cssText = 'position:fixed;top:20px;right:20px;background:#1877f2;color:white;padding:15px;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.3);z-index:9999;max-width:300px;animation:slideIn 0.3s ease;';
            alert.innerHTML = '<strong>🔔 New Activity!</strong><br>' + message;
            document.body.appendChild(alert);
            
            // Auto-remove after 4 seconds
            setTimeout(() => {
                alert.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => alert.remove(), 300);
            }, 4000);
        }
        
        function checkForNewPosts() {
            if (notificationInterval) {
                clearInterval(notificationInterval);
            }
            
            notificationInterval = setInterval(function() {
                if (!notificationsEnabled) return;
                
                fetch('check_posts.php')
                    .then(response => response.json())
                    .then(data => {
                        if (lastPostCount > 0 && data.count > lastPostCount) {
                            // Try native notification first
                            if ('Notification' in window && Notification.permission === 'granted') {
                                new Notification('New Post!', {
                                    body: data.latest_post,
                                    icon: 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="%231877f2"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>'
                                });
                            } else {
                                // Fallback visual alert for iOS/unsupported
                                showVisualAlert(data.latest_post);
                            }
                            
                            // Update page title for additional notification
                            document.title = '🔔 New Post - OSRG Connect';
                            setTimeout(() => document.title = 'Feed - OSRG Connect', 3000);
                        }
                        lastPostCount = data.count;
                    });
            }, 5000);
        }
        
        window.onload = function() {
            // Check if notifications were previously enabled
            const savedPreference = localStorage.getItem('notificationsEnabled');
            
            if (savedPreference === 'true') {
                // Restore enabled state
                if ('Notification' in window && Notification.permission === 'granted') {
                    notificationsEnabled = true;
                    document.getElementById('notif-btn').textContent = 'Disable Notifications';
                    document.getElementById('notif-btn').style.background = '#4caf50';
                    checkForNewPosts();
                } else if ('Notification' in window && Notification.permission === 'denied') {
                    notificationsEnabled = true;
                    document.getElementById('notif-btn').textContent = 'Disable Notifications';
                    document.getElementById('notif-btn').style.background = '#ff9800';
                    checkForNewPosts();
                } else {
                    // Permission not granted yet, show enable button
                    document.getElementById('notif-btn').textContent = 'Enable Notifications';
                    document.getElementById('notif-btn').style.background = '#666';
                }
            } else {
                // Default disabled state
                document.getElementById('notif-btn').textContent = 'Enable Notifications';
                document.getElementById('notif-btn').style.background = '#666';
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
        <a href="settings.php">Settings</a>
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
            <h1>Public Feed</h1>
            <button id="notif-btn" class="notification-btn" onclick="toggleNotifications()">Enable Notifications</button>
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
            <form method="POST" enctype="multipart/form-data" id="postForm">
                <div class="form-group">
                    <div id="editor" style="border: 1px solid #ddd; border-radius: 5px; min-height: 100px; padding: 10px; background: white;"></div>
                    <textarea name="content" id="content" style="display: none;"></textarea>
                </div>
                <div class="form-group">
                    <input type="file" name="file" accept=".mp4,.mp3,.png,.jpg,.jpeg" style="margin-bottom: 10px;">
                    <small style="color: #666;">Upload: MP4, MP3, PNG, JPG (max 10MB)</small>
                </div>
                <button type="submit">Post</button>
            </form>
        </div>

        <?php if ($posts): ?>
            <?php foreach ($posts as $post): ?>
            <div class="post">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                    <?php if ($post['avatar']): ?>
                        <?php if (strpos($post['avatar'], 'avatars/') === 0): ?>
                            <img src="<?= htmlspecialchars($post['avatar']) ?>" alt="Avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                        <?php else: ?>
                            <span style="font-size: 30px;"><?= htmlspecialchars($post['avatar']) ?></span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span style="font-size: 30px;">👤</span>
                    <?php endif; ?>
                    <strong><?= htmlspecialchars($post['username']) ?></strong>
                </div>
                <div><?= $post['content'] ?></div>
                
                <?php if ($post['file_path']): ?>
                <div style="margin: 10px 0;">
                    <?php if ($post['file_type'] == 'mp4'): ?>
                        <video controls style="width: 100%; max-width: 100%; display: block;">
                            <source src="<?= $post['file_path'] ?>" type="video/mp4">
                        </video>
                    <?php elseif ($post['file_type'] == 'mp3'): ?>
                        <audio controls preload="metadata" style="width: 100%; display: block;">
                            <source src="<?= $post['file_path'] ?>" type="audio/mpeg">
                            <source src="<?= $post['file_path'] ?>" type="audio/mp3">
                            Your browser does not support the audio element.
                        </audio>
                    <?php elseif (in_array($post['file_type'], ['png', 'jpg', 'jpeg'])): ?>
                        <img src="<?= htmlspecialchars($post['file_path']) ?>" alt="Uploaded image" style="width: 100%; max-width: 100%; display: block; border-radius: 8px;">
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- Reactions -->
                <div style="margin: 15px 0; padding: 10px 0; border-top: 1px solid #eee;">
                    <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                            <button type="submit" name="reaction" value="<?= $post['user_reaction'] === 'like' ? 'remove' : 'like' ?>" 
                                    style="background: none; border: none; font-size: 16px; cursor: pointer; <?= $post['user_reaction'] === 'like' ? 'color: #1877f2;' : '' ?>">
                                👍 <?= $post['reaction_count'] > 0 ? $post['reaction_count'] : '' ?>
                            </button>
                        </form>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                            <button type="submit" name="reaction" value="<?= $post['user_reaction'] === 'love' ? 'remove' : 'love' ?>" 
                                    style="background: none; border: none; font-size: 16px; cursor: pointer; <?= $post['user_reaction'] === 'love' ? 'color: #e91e63;' : '' ?>">
                                ❤️
                            </button>
                        </form>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                            <button type="submit" name="reaction" value="<?= $post['user_reaction'] === 'laugh' ? 'remove' : 'laugh' ?>" 
                                    style="background: none; border: none; font-size: 16px; cursor: pointer; <?= $post['user_reaction'] === 'laugh' ? 'color: #ff9800;' : '' ?>">
                                😂
                            </button>
                        </form>
                        <span style="color: #666; font-size: 14px;">💬 <?= $post['comment_count'] ?></span>
                    </div>
                    
                    <!-- Comments -->
                    <?php
                    $stmt_comments = $pdo->prepare("SELECT c.content, c.created_at, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.created_at ASC");
                    $stmt_comments->execute([$post['id']]);
                    $comments = $stmt_comments->fetchAll();
                    ?>
                    
                    <?php if ($comments): ?>
                        <?php foreach ($comments as $comment): ?>
                        <div style="background: #f8f9fa; padding: 8px; margin: 5px 0; border-radius: 5px; font-size: 14px;">
                            <strong><?= htmlspecialchars($comment['username']) ?>:</strong> 
                            <?= htmlspecialchars($comment['content']) ?>
                            <small style="color: #666; margin-left: 10px;"><?= date('M j, H:i', strtotime($comment['created_at'])) ?></small>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <!-- Add Comment -->
                    <form method="POST" style="margin-top: 10px;">
                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                        <div style="display: flex; gap: 10px;">
                            <input type="text" name="comment" placeholder="Write a comment..." style="flex: 1; padding: 8px; border: 1px solid #ddd; border-radius: 20px; font-size: 14px;" required>
                            <button type="submit" style="padding: 8px 15px; font-size: 12px;">Post</button>
                        </div>
                    </form>
                </div>
                
                <div style="clear: both; margin-top: 10px;">
                    <small><?php
                    // Get user's timezone
                    $stmt_tz = $pdo->prepare("SELECT timezone FROM users WHERE id = ?");
                    $stmt_tz->execute([$_SESSION['user_id']]);
                    $user_tz = $stmt_tz->fetch();
                    $timezone = $user_tz['timezone'] ?? 'Europe/London';
                    
                    // Convert to user's timezone
                    $date = new DateTime($post['created_at'], new DateTimeZone('UTC'));
                    $date->setTimezone(new DateTimeZone($timezone));
                    echo $date->format('M j, H:i');
                    ?></small>
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