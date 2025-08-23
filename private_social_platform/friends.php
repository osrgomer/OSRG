<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = get_db();

// Get all friends (accepted friendships)
$stmt = $pdo->prepare("
    SELECT DISTINCT u.id, u.username, f.created_at
    FROM users u
    JOIN friends f ON (
        (f.user_id = ? AND f.friend_id = u.id AND f.status = 'accepted') OR
        (f.friend_id = ? AND f.user_id = u.id AND f.status = 'accepted')
    )
    ORDER BY f.created_at DESC
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$friends = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Friends - Private Social</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
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
        <a href="logout.php">Logout</a>
    </div>
    
    <div class="container">
        <div class="header">
            <h1>My Friends</h1>
        </div>

        <div class="post">
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
</body>
</html>