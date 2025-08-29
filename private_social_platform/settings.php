<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = get_db();
$message = '';

// Get current user settings
$stmt = $pdo->prepare("SELECT username, email, timezone, email_notifications, avatar FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
$current_timezone = $user['timezone'] ?? 'Europe/London';
$email_notifications = $user['email_notifications'] ?? 0;
$current_username = $user['username'] ?? '';
$current_email = $user['email'] ?? '';
$current_avatar = $user['avatar'] ?? '';

// Handle profile update
if ($_POST['update_profile'] ?? false) {
    $new_username = $_POST['username'];
    $new_email = $_POST['email'];
    $avatar = $current_avatar;
    
    // Handle avatar upload
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $allowed = ['png', 'jpg', 'jpeg'];
        $file_ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        $file_size = $_FILES['avatar']['size'];
        $max_size = 2 * 1024 * 1024; // 2MB limit
        
        if ($file_size <= $max_size && in_array($file_ext, $allowed)) {
            if (!is_dir('avatars')) {
                mkdir('avatars', 0755, true);
            }
            $avatar_filename = 'avatar_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_ext;
            $avatar_path = 'avatars/' . $avatar_filename;
            
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar_path)) {
                $avatar = $avatar_path;
            }
        }
    } elseif ($_POST['preset_avatar'] ?? false) {
        $avatar = $_POST['preset_avatar'];
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, avatar = ? WHERE id = ?");
        $stmt->execute([$new_username, $new_email, $avatar, $_SESSION['user_id']]);
        $message = 'Profile updated successfully!';
        $current_username = $new_username;
        $current_email = $new_email;
        $current_avatar = $avatar;
    } catch (PDOException $e) {
        $message = 'Error: Username or email already exists.';
    }
}

// Handle settings update
if ($_POST['timezone'] ?? false) {
    $email_notif = isset($_POST['email_notifications']) ? 1 : 0;
    $stmt = $pdo->prepare("UPDATE users SET timezone = ?, email_notifications = ? WHERE id = ?");
    $stmt->execute([$_POST['timezone'], $email_notif, $_SESSION['user_id']]);
    $message = 'Settings updated successfully!';
    $current_timezone = $_POST['timezone'];
    $email_notifications = $email_notif;
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
        
        <!-- Profile Settings -->
        <div class="post">
            <h3>Edit Profile</h3>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label><strong>Username:</strong></label>
                    <input type="text" name="username" value="<?= htmlspecialchars($current_username) ?>" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
                <div class="form-group">
                    <label><strong>Email:</strong></label>
                    <input type="email" name="email" value="<?= htmlspecialchars($current_email) ?>" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
                <div class="form-group">
                    <label><strong>Avatar:</strong></label>
                    <?php if ($current_avatar): ?>
                        <div style="margin: 10px 0;">
                            <?php if (strpos($current_avatar, 'avatars/') === 0): ?>
                                <img src="<?= htmlspecialchars($current_avatar) ?>" alt="Current Avatar" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;">
                            <?php else: ?>
                                <span style="font-size: 60px;"><?= htmlspecialchars($current_avatar) ?></span>
                            <?php endif; ?>
                            <small style="display: block; color: #666;">Current Avatar</small>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="avatar" accept=".png,.jpg,.jpeg" style="margin-bottom: 10px;">
                    <small style="color: #666; display: block;">Upload custom avatar (PNG, JPG - max 2MB)</small>
                    
                    <div style="margin: 15px 0;">
                        <label><strong>Or choose preset avatar:</strong></label>
                        <div style="display: flex; gap: 10px; margin-top: 10px; flex-wrap: wrap;">
                            <label style="cursor: pointer;">
                                <input type="radio" name="preset_avatar" value="👤" style="margin-right: 5px;">
                                <span style="font-size: 40px;">👤</span>
                            </label>
                            <label style="cursor: pointer;">
                                <input type="radio" name="preset_avatar" value="👨" style="margin-right: 5px;">
                                <span style="font-size: 40px;">👨</span>
                            </label>
                            <label style="cursor: pointer;">
                                <input type="radio" name="preset_avatar" value="👩" style="margin-right: 5px;">
                                <span style="font-size: 40px;">👩</span>
                            </label>
                            <label style="cursor: pointer;">
                                <input type="radio" name="preset_avatar" value="🧑" style="margin-right: 5px;">
                                <span style="font-size: 40px;">🧑</span>
                            </label>
                            <label style="cursor: pointer;">
                                <input type="radio" name="preset_avatar" value="👶" style="margin-right: 5px;">
                                <span style="font-size: 40px;">👶</span>
                            </label>
                            <label style="cursor: pointer;">
                                <input type="radio" name="preset_avatar" value="🐱" style="margin-right: 5px;">
                                <span style="font-size: 40px;">🐱</span>
                            </label>
                            <label style="cursor: pointer;">
                                <input type="radio" name="preset_avatar" value="🐶" style="margin-right: 5px;">
                                <span style="font-size: 40px;">🐶</span>
                            </label>
                            <label style="cursor: pointer;">
                                <input type="radio" name="preset_avatar" value="🦊" style="margin-right: 5px;">
                                <span style="font-size: 40px;">🦊</span>
                            </label>
                        </div>
                    </div>
                </div>
                <button type="submit" name="update_profile" value="1">Update Profile</button>
            </form>
        </div>

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
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" name="email_notifications" <?= $email_notifications ? 'checked' : '' ?>>
                        <strong>Send me notifications by email</strong>
                    </label>
                    <small style="color: #666; margin-left: 30px;">Get email alerts when you receive new messages</small>
                </div>
                <button type="submit">Update Settings</button>
            </form>
        </div>

        <div class="post">
            <h3>About Timezones</h3>
            <p>Setting your timezone ensures that all timestamps (posts, messages, etc.) are displayed in your local time. The default timezone is London (GMT/BST).</p>
        </div>
    </div>
</body>
</html>