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

// Get search term
$search = $_GET['search'] ?? '';

// Get users with their friendship status
if ($search) {
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
        WHERE u.id != ? AND u.username LIKE ?
        GROUP BY u.id, u.username
        ORDER BY u.username
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], '%' . $search . '%']);
} else {
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
        ORDER BY u.username
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
}
$users = $stmt->fetchAll();
// Set page variables for header
$page_title = 'Find Friends - OSRG Connect';
$additional_css = '
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1877f2; color: white; padding: 15px; text-align: center; }
        .post { background: white; padding: 15px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .user-item { display: flex; justify-content: space-between; align-items: center; padding: 10px; border-bottom: 1px solid #eee; }
        .add-btn { background: #1877f2; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; }
        .message { color: green; padding: 10px; }
';

require_once 'header.php';
?>
    
    <div class="container">
        <div class="header">
            <h1>Find Friends</h1>
        </div>

        <?php if (isset($message)): ?>
            <div class="message"><?= $message ?></div>
        <?php endif; ?>
        
        <!-- Search Form -->
        <div class="post">
            <form method="GET" style="display: flex; gap: 10px; align-items: center;">
                <input type="text" name="search" placeholder="Search by username..." value="<?= htmlspecialchars($search) ?>" style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                <button type="submit" style="padding: 10px 20px; background: #1877f2; color: white; border: none; border-radius: 5px; cursor: pointer;">Search</button>
                <?php if ($search): ?>
                    <a href="users.php" style="padding: 10px 15px; background: #666; color: white; text-decoration: none; border-radius: 5px;">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="post">
            <?php if ($search): ?>
                <h3>Search Results for "<?= htmlspecialchars($search) ?>" (<?= count($users) ?> found)</h3>
            <?php else: ?>
                <h3>All Users</h3>
            <?php endif; ?>
            
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
                <?php if ($search): ?>
                    <p style="text-align: center; color: #666; padding: 20px;">No users found matching "<?= htmlspecialchars($search) ?>"</p>
                <?php else: ?>
                    <p style="text-align: center; color: #666; padding: 20px;">No other users found.</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>