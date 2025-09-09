<?php
require_once 'config.php';

$error = '';

if ($_POST['username'] ?? false) {
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT id, password_hash, approved FROM users WHERE username = ?");
    $stmt->execute([$_POST['username']]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($_POST['password'], $user['password_hash'])) {
        if ($user['approved']) {
            $_SESSION['user_id'] = $user['id'];
            header('Location: ' . OSRG_CONNECT_BASE_URL); // Redirect to the main plugin page
            exit;
        } else {
            $error = 'Account pending admin approval';
        }
    } else {
        $error = 'Invalid credentials';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <title>Login - OSRG Connect</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .container { background-color: #fff; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1), 0 8px 16px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center; }
        h1 { color: #1c1e21; font-size: 24px; margin-bottom: 10px; }
        p { color: #606770; font-size: 14px; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        input[type="text"], input[type="password"] { width: calc(100% - 22px); padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 16px; }
        button { width: 100%; padding: 12px; background-color: #1877f2; border: none; color: #fff; border-radius: 6px; font-size: 18px; font-weight: bold; cursor: pointer; }
        .error { color: red; margin-bottom: 15px; }
        .links a { display: block; margin-top: 15px; color: #1877f2; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Log In</h1>
        <p>Log in to your OSRG Connect account</p>

        <?php if ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="form-group">
                <button type="submit">Log In</button>
            </div>
        </form>

        <div class="links">
            <a href="<?= OSRG_CONNECT_BASE_URL ?>?page=forgot-password">Forgot password?</a>
            <a href="<?= OSRG_CONNECT_BASE_URL ?>?page=register">Create New Account</a>
        </div>
    </div>
</body>
</html>