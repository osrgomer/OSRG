<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = get_db();

// Check if user is admin (OSRG)
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user || $user['username'] !== 'OSRG') {
    header('Location: index.php');
    exit;
}

$message = '';

// Handle user deletion
if ($_GET['delete'] ?? false) {
    $delete_id = $_GET['delete'];
    if ($delete_id != $_SESSION['user_id']) { // Can't delete self
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$delete_id]);
        
        // Clean up related data
        $stmt = $pdo->prepare("DELETE FROM posts WHERE user_id = ?");
        $stmt->execute([$delete_id]);
        
        $stmt = $pdo->prepare("DELETE FROM friends WHERE user_id = ? OR friend_id = ?");
        $stmt->execute([$delete_id, $delete_id]);
        
        $stmt = $pdo->prepare("DELETE FROM messages WHERE sender_id = ? OR receiver_id = ?");
        $stmt->execute([$delete_id, $delete_id]);
        
        $message = 'User deleted successfully!';
    }
}

// Handle post deletion
if ($_GET['delete_post'] ?? false) {
    $post_id = $_GET['delete_post'];
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);
    $message = 'Post deleted successfully!';
}

// Get all users
$stmt = $pdo->query("SELECT id, username, email, created_at FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

// Get all posts
$stmt = $pdo->query("SELECT p.id, p.content, p.created_at, u.username FROM posts p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC");
$posts = $stmt->fetchAll();

// Get stats
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
$user_count = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM posts");
$post_count = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM messages");
$message_count = $stmt->fetch()['count'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel - Private Social</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; padding: 20px; }
        .header { background: #d32f2f; color: white; padding: 15px; text-align: center; }
        .nav { background: white; padding: 10px; margin-bottom: 20px; border-radius: 8px; }
        .nav a { color: #1877f2; text-decoration: none; margin-right: 15px; }
        .admin-nav { color: #d32f2f; font-weight: bold; }
        .post { background: white; padding: 15px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .stats { display: flex; gap: 20px; margin-bottom: 20px; }
        .stat-box { background: white; padding: 20px; border-radius: 8px; text-align: center; flex: 1; }
        .stat-number { font-size: 24px; font-weight: bold; color: #1877f2; }
        .user-item, .post-item { display: flex; justify-content: space-between; align-items: center; padding: 10px; border-bottom: 1px solid #eee; }
        .delete-btn { background: #f44336; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; }
        .message { color: green; padding: 10px; background: #e8f5e8; border-radius: 5px; margin-bottom: 10px; }
        .tabs { display: flex; gap: 10px; margin-bottom: 20px; }
        .tab { padding: 10px 20px; background: white; border-radius: 5px; cursor: pointer; }
        .tab.active { background: #1877f2; color: white; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
    </style>
    <script>
        function showTab(tabName) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
            document.querySelector('[onclick="showTab(\'' + tabName + '\')"]').classList.add('active');
            document.getElementById(tabName).classList.add('active');
        }
    </script>
</head>
<body>
    <div class="nav">
        <a href="index.php">Home</a>
        <a href="users.php">Find Friends</a>
        <a href="friends.php">My Friends</a>
        <a href="messages.php">Messages</a>
        <a href="admin.php" class="admin-nav">Admin Panel</a>
        <a href="logout.php">Logout</a>
    </div>
    
    <div class="container">
        <div class="header">
            <h1>🛡️ Admin Panel</h1>
            <p>Welcome, OSRG</p>
        </div>

        <?php if ($message): ?>
            <div class="message"><?= $message ?></div>
        <?php endif; ?>

        <div class="stats">
            <div class="stat-box">
                <div class="stat-number"><?= $user_count ?></div>
                <div>Total Users</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?= $post_count ?></div>
                <div>Total Posts</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?= $message_count ?></div>
                <div>Total Messages</div>
            </div>
        </div>

        <div class="tabs">
            <div class="tab active" onclick="showTab('users')">Manage Users</div>
            <div class="tab" onclick="showTab('posts')">Manage Posts</div>
        </div>

        <div id="users" class="tab-content active">
            <div class="post">
                <h3>All Users</h3>
                <?php foreach ($users as $user): ?>
                <div class="user-item">
                    <div>
                        <strong><?= htmlspecialchars($user['username']) ?></strong><br>
                        <small><?= htmlspecialchars($user['email']) ?> • Joined <?= date('M j, Y', strtotime($user['created_at'])) ?></small>
                    </div>
                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                        <a href="?delete=<?= $user['id'] ?>" class="delete-btn" onclick="return confirm('Delete this user?')">Delete</a>
                    <?php else: ?>
                        <span style="color: #4caf50;">Admin</span>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div id="posts" class="tab-content">
            <div class="post">
                <h3>All Posts</h3>
                <?php foreach ($posts as $post): ?>
                <div class="post-item">
                    <div>
                        <strong><?= htmlspecialchars($post['username']) ?></strong><br>
                        <span><?= htmlspecialchars(substr($post['content'], 0, 100)) ?>...</span><br>
                        <small><?= $post['created_at'] ?></small>
                    </div>
                    <a href="?delete_post=<?= $post['id'] ?>" class="delete-btn" onclick="return confirm('Delete this post?')">Delete</a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>