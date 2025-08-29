<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = get_db();

// Get all friends (accepted friendships)
$stmt = $pdo->prepare("
    SELECT u.id, u.username, MIN(f.created_at) as created_at
    FROM users u
    JOIN friends f ON (
        (f.user_id = ? AND f.friend_id = u.id AND f.status = 'accepted') OR
        (f.friend_id = ? AND f.user_id = u.id AND f.status = 'accepted')
    )
    GROUP BY u.id, u.username
    ORDER BY created_at DESC
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$friends = $stmt->fetchAll();

// Get posts from friends only
try {
    $stmt = $pdo->prepare("
        SELECT p.content, p.created_at, u.username, p.file_path, p.file_type
        FROM posts p 
        JOIN users u ON p.user_id = u.id
        JOIN friends f ON (
            (f.user_id = ? AND f.friend_id = p.user_id AND f.status = 'accepted') OR
            (f.friend_id = ? AND f.user_id = p.user_id AND f.status = 'accepted')
        )
        GROUP BY p.id
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    $friend_posts = $stmt->fetchAll();
} catch (Exception $e) {
    // Fallback for old database without file columns
    $stmt = $pdo->prepare("
        SELECT p.content, p.created_at, u.username, NULL as file_path, NULL as file_type
        FROM posts p 
        JOIN users u ON p.user_id = u.id
        JOIN friends f ON (
            (f.user_id = ? AND f.friend_id = p.user_id AND f.status = 'accepted') OR
            (f.friend_id = ? AND f.user_id = p.user_id AND f.status = 'accepted')
        )
        GROUP BY p.id
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    $friend_posts = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Friends - Private Social</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .content-wrapper { display: flex; gap: 20px; }
        .friends-sidebar { width: 300px; }
        .feed-main { flex: 1; }
        
        @media (max-width: 768px) {
            .content-wrapper { flex-direction: column; }
            .friends-sidebar { width: 100%; }
        }
        .header { background: #1877f2; color: white; padding: 15px; text-align: center; }
        .nav { background: white; padding: 10px; margin-bottom: 20px; border-radius: 8px; }
        .nav a { color: #1877f2; text-decoration: none; margin-right: 15px; }
        .post { background: white; padding: 15px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .friend-item { display: flex; justify-content: space-between; align-items: center; padding: 15px; border-bottom: 1px solid #eee; }
        .friend-info { display: flex; flex-direction: column; }
        .friend-name { font-weight: bold; font-size: 16px; }
        .friend-since { color: #666; font-size: 12px; }
    </style>
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
            <h1>My Friends</h1>
        </div>

        <div class="content-wrapper">
            <!-- Left Sidebar: Friends List -->
            <div class="friends-sidebar">
                <div class="post">
                    <h3>Friends List</h3>
                    <?php if ($friends): ?>
                        <?php foreach ($friends as $friend): ?>
                        <div class="friend-item">
                            <div class="friend-info">
                                <span class="friend-name"><?= htmlspecialchars($friend['username']) ?></span>
                                <span class="friend-since">Friends since <?= date('M j, Y', strtotime($friend['created_at'])) ?></span>
                            </div>
                            <span style="color: #4caf50; font-size: 18px;">✓</span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: #666; padding: 20px;">
                            You don't have any friends yet.<br>
                            <a href="users.php" style="color: #1877f2;">Find some friends!</a>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Main: Friends Feed -->
            <div class="feed-main">
                <div class="header" style="margin-top: 0; margin-bottom: 20px;">
                    <h1>Friends Feed</h1>
                </div>

                <?php if ($friend_posts): ?>
                    <?php foreach ($friend_posts as $post): ?>
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
                        <p style="text-align: center; color: #666; padding: 20px;">
                            No posts from friends yet.<br>
                            <?php if (!$friends): ?>
                                <a href="users.php" style="color: #1877f2;">Add some friends to see their posts!</a>
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>