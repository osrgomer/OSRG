<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = get_db();
$message = '';

// Get current user timezone
$stmt = $pdo->prepare("SELECT timezone FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
$current_timezone = $user['timezone'] ?? 'Europe/London';

// Handle timezone update
if ($_POST['timezone'] ?? false) {
    $stmt = $pdo->prepare("UPDATE users SET timezone = ? WHERE id = ?");
    $stmt->execute([$_POST['timezone'], $_SESSION['user_id']]);
    $message = 'Timezone updated successfully!';
    $current_timezone = $_POST['timezone'];
}

// Common timezones
$timezones = [
    'Europe/London' => 'London (GMT/BST)',
    'Europe/Paris' => 'Paris (CET/CEST)',
    'Europe/Berlin' => 'Berlin (CET/CEST)',
    'Europe/Amsterdam' => 'Amsterdam (CET/CEST)',
    'Europe/Rome' => 'Rome (CET/CEST)',
    'Europe/Madrid' => 'Madrid (CET/CEST)',
    'America/New_York' => 'New York (EST/EDT)',
    'America/Los_Angeles' => 'Los Angeles (PST/PDT)',
    'America/Chicago' => 'Chicago (CST/CDT)',
    'America/Toronto' => 'Toronto (EST/EDT)',
    'Asia/Tokyo' => 'Tokyo (JST)',
    'Asia/Shanghai' => 'Shanghai (CST)',
    'Asia/Dubai' => 'Dubai (GST)',
    'Australia/Sydney' => 'Sydney (AEST/AEDT)',
    'Pacific/Auckland' => 'Auckland (NZST/NZDT)'
];
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Private Social</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1877f2; color: white; padding: 15px; text-align: center; }
        .nav { background: white; padding: 10px; margin-bottom: 20px; border-radius: 8px; }
        .nav a { color: #1877f2; text-decoration: none; margin-right: 15px; }
        .post { background: white; padding: 15px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .form-group { margin: 15px 0; }
        select, button { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px; }
        button { background: #1877f2; color: white; border: none; cursor: pointer; }
        .message { color: green; padding: 10px; background: #e8f5e8; border-radius: 5px; margin-bottom: 10px; }
        .current-time { background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px 0; }
        
        @media (max-width: 768px) {
            .container { padding: 15px; margin: 10px; }
        }
    </style>
</head>
<body>
    <div class="nav">
        <a href="index.php">Home</a>
        <a href="users.php">Find Friends</a>
        <a href="friends.php">My Friends</a>
        <a href="messages.php">Messages</a>
        <a href="settings.php" style="font-weight: bold;">Settings</a>
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
            <h1>⚙️ Settings</h1>
        </div>

        <?php if ($message): ?>
            <div class="message"><?= $message ?></div>
        <?php endif; ?>

        <div class="post">
            <h3>Timezone Settings</h3>
            
            <div class="current-time">
                <strong>Current time in your timezone:</strong><br>
                <?php
                date_default_timezone_set($current_timezone);
                echo date('l, F j, Y - H:i:s T');
                ?>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label><strong>Select your timezone:</strong></label>
                    <select name="timezone" required>
                        <?php foreach ($timezones as $tz => $label): ?>
                            <option value="<?= $tz ?>" <?= $tz === $current_timezone ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit">Update Timezone</button>
            </form>
        </div>

        <div class="post">
            <h3>About Timezones</h3>
            <p>Setting your timezone ensures that all timestamps (posts, messages, etc.) are displayed in your local time. The default timezone is London (GMT/BST).</p>
        </div>
    </div>
</body>
</html>