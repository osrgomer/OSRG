<?php
require_once 'config.php';

$message = '';
$error = '';

if ($_POST['email'] ?? false) {
    $pdo = get_db();
    $stmt = $pdo->prepare("SELECT id, username, email FROM users WHERE email = ?");
    $stmt->execute([$_POST['email']]);
    $user = $stmt->fetch();
    
    if ($user) {
        // Generate reset token
        $reset_token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Store reset token in database
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS password_resets (
                id INTEGER PRIMARY KEY,
                user_id INTEGER,
                token TEXT,
                expires DATETIME,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");
        } catch (Exception $e) {
            // Table already exists
        }
        
        $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires) VALUES (?, ?, ?)");
        $stmt->execute([$user['id'], $reset_token, $expires]);
        
        // Send email
        $reset_link = "https://osrg.lol/osrg/private_social_platform/reset_password.php?token=" . $reset_token;
        $subject = "Password Reset - OSRG Connect";
        $body = "Hi " . $user['username'] . ",\n\n";
        $body .= "You requested a password reset for your OSRG Connect account.\n\n";
        $body .= "Click the link below to reset your password:\n";
        $body .= $reset_link . "\n\n";
        $body .= "This link will expire in 1 hour.\n\n";
        $body .= "If you didn't request this, please ignore this email.\n\n";
        $body .= "Best regards,\nOSRG Connect Team";
        
        $headers = "From: OSRG Connect <omer@osrg.lol>\r\n";
        $headers .= "Reply-To: omer@osrg.lol\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        // Configure SMTP
        ini_set('SMTP', 'smtp.hostinger.com');
        ini_set('smtp_port', '465');
        ini_set('sendmail_from', 'omer@osrg.lol');
        
        if (mail($user['email'], $subject, $body, $headers)) {
            $message = 'Password reset link sent to your email address.';
        } else {
            $error = 'Failed to send email. Please try again later.';
        }
    } else {
        $error = 'No account found with that email address.';
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
    <title>Forgot Password - OSRG Connect</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1877f2; color: white; padding: 15px; text-align: center; }
        .form-group { margin: 15px 0; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px; }
        button { background: #1877f2; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        .message { color: green; padding: 10px; background: #e8f5e8; border-radius: 5px; margin-bottom: 10px; }
        .error { color: red; padding: 10px; background: #ffeaea; border-radius: 5px; margin-bottom: 10px; }
        
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
            <h1>Forgot Password</h1>
            <p>Reset your OSRG Connect password</p>
        </div>

        <?php if ($message): ?>
            <div class="message"><?= $message ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <input type="email" name="email" placeholder="Enter your email address" required>
            </div>
            <div class="form-group">
                <button type="submit">Send Reset Link</button>
            </div>
        </form>

        <p style="text-align: center; margin-top: 20px;">
            Remember your password? <a href="login.php">Login here</a>
        </p>
    </div>
</body>
</html>