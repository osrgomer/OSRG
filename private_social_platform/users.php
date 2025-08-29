<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = get_db();

// Handle add friend
if ($_GET['add'] ?? false) {
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO friends (user_id, friend_id, status) VALUES (?, ?, 'pending')");
    $stmt->execute([$_SESSION['user_id'], $_GET['add']]);
    $message = 'Friend request sent!';
}

// Get users with their friendship status
$stmt = $pdo->prepare("
    SELECT u.id, u.username,
           CASE 
               WHEN MAX(CASE WHEN f1.status = 'accepted' OR f2.status = 'accepted' THEN 1 ELSE 0 END) = 1 THEN 'friends'
               WHEN MAX(CASE WHEN f1.status = 'pending' THEN 1 ELSE 0 END) = 1 THEN 'request_sent'
               WHEN MAX(CASE WHEN f2.status = 'pending' THEN 1 ELSE 0 END) = 1 THEN 'request_received'
               ELSE 'none'
           END as friendship_status
    FROM users u
    LEFT JOIN friends f1 ON (u.id = f1.friend_id AND f1.user_id = ?)
    LEFT JOIN friends f2 ON (u.id = f2.user_id AND f2.friend_id = ?)
    WHERE u.id != ?
    GROUP BY u.id, u.username
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <title>Find Friends - OSRG Connect</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1877f2; color: white; padding: 15px; text-align: center; }
        .nav { background: white; padding: 10px; margin-bottom: 20px; border-radius: 8px; }
        .nav a { color: #1877f2; text-decoration: none; margin-right: 15px; }
        .post { background: white; padding: 15px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .user-item { display: flex; justify-content: space-between; align-items: center; padding: 10px; border-bottom: 1px solid #eee; }
        .add-btn { background: #1877f2; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; }
        .message { color: green; padding: 10px; }
    </style>
</head>
<body>
    <div class="nav">
        <a href="index.php">Home</a>
        <a href="users.php">Find Friends</a>
        <a href="friends.php">My Friends</a>
        <a href="messages.php">Messages</a>
        <a href="logout.php">Logout</a>
    </div>
    
    <div class="container">
        <div class="header">
            <h1>Find Friends</h1>
        </div>

        <?php if (isset($message)): ?>
            <div class="message"><?= $message ?></div>
        <?php endif; ?>

        <div class="post">
            <?php if ($users): ?>
                <?php foreach ($users as $user): ?>
                <div class="user-item">
                    <span><strong><?= htmlspecialchars($user['username']) ?></strong></span>
                    <?php if ($user['friendship_status'] == 'friends'): ?>
                        <span style="color: #4caf50; font-weight: bold;">✓ Friends</span>
                    <?php elseif ($user['friendship_status'] == 'request_sent'): ?>
                        <span style="color: #ff9800;">Request Sent</span>
                    <?php elseif ($user['friendship_status'] == 'request_received'): ?>
                        <span style="color: #2196f3;">Pending Request</span>
                    <?php else: ?>
                        <a href="?add=<?= $user['id'] ?>" class="add-btn">Add Friend</a>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No other users found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>