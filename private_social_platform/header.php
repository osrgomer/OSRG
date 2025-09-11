<?php
// Ensure user is logged in for header display
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <?= isset($mobile_viewport) ? $mobile_viewport : '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">' ?>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <link rel="manifest" href="site.webmanifest">
    <title><?= $page_title ?? 'OSRG Connect' ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .nav { background: white; padding: 10px; margin-bottom: 20px; border-radius: 8px; }
        .nav a { color: #1877f2; text-decoration: none; margin-right: 15px; }
        <?= $additional_css ?? '' ?>
    </style>
    <?= $additional_head ?? '' ?>
</head>
<body>
    <div class="nav">
        <a href="home">Home</a>
        <a href="find-friends">Find Friends</a>
        <a href="friends">My Friends</a>
        <a href="messages">Messages</a>
        <a href="settings">Settings</a>
        <?php
        $pdo_nav = get_db();
        $stmt_nav = $pdo_nav->prepare("SELECT username FROM users WHERE id = ?");
        $stmt_nav->execute([$_SESSION['user_id']]);
        $user_nav = $stmt_nav->fetch();
        if ($user_nav && $user_nav['username'] === 'OSRG'):
        ?>
        <a href="admin" style="color: #d32f2f; font-weight: bold;">Admin Panel</a>
        <?php endif; ?>
        <a href="logout.php">Logout</a>
    </div>
    <div class="container">