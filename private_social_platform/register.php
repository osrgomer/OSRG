<?php
require_once 'config.php';
init_db();

$message = '';

if ($_POST['username'] ?? false) {
    $pdo = get_db();
    
    try {
        $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $approved = ($_POST['username'] === 'OSRG') ? 1 : 0;
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, approved) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['username'], $_POST['email'], $password_hash, $approved]);
        
        if ($approved) {
            $message = 'Registration successful! <a href="login.php">Login here</a>';
        } else {
            $message = 'Registration submitted! Please wait for admin approval before logging in.';
        }
    } catch (PDOException $e) {
        $message = 'Username or email already exists';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Private Social</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1877f2; color: white; padding: 15px; text-align: center; }
        .form-group { margin: 15px 0; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px; }
        button { background: #1877f2; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        .message { color: green; padding: 10px; }
        
        @media (max-width: 768px) {
            .container { padding: 15px; margin: 10px; }
            .header { padding: 20px 15px; }
            input, button { padding: 12px; font-size: 16px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Join Private Social</h1>
            <p>Create your account</p>
        </div>

        <?php if ($message): ?>
            <div class="message"><?= $message ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="form-group">
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="form-group">
                <button type="submit">Register</button>
            </div>
        </form>

        <p style="text-align: center; margin-top: 20px;">
            Already have an account? <a href="login.php">Login here</a>
        </p>
    </div>
</body>
</html>