<?php
require_once 'config.php';
// The login check is now handled by the main plugin file, so this is no longer needed.
// if (!isset($_SESSION['user_id'])) {
//     header('Location: login.php');
//     exit;
// }
?>
<!DOCTYPE html>
<html>
<head>
    <?= isset($mobile_viewport) ? $mobile_viewport : '<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">' ?>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
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
        <a href="<?= OSRG_CONNECT_BASE_URL ?>">Home</a>
        <a href="<?= OSRG_CONNECT_BASE_URL ?>?page=users">Find Friends</a>
        <a href="<?= OSRG_CONNECT_BASE_URL ?>?page=friends">My Friends</a>
        <a href="<?= OSRG_CONNECT_BASE_URL ?>?page=messages">Messages</a>
        <a href="<?= OSRG_CONNECT_BASE_URL ?>?page=settings">Settings</a>
        <a href="<?= wp_logout_url(OSRG_CONNECT_BASE_URL); ?>">Logout</a>
    </div>