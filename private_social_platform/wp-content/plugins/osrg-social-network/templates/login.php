<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Handle login
if ($_POST['username'] ?? false) {
    $user = osrg_wp_social_authenticate($_POST['username'], $_POST['password']);
    if ($user) {
        osrg_social_login_user($user['id']);
        wp_redirect(home_url());
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}

// If already logged in, redirect
if (osrg_social_is_logged_in()) {
    wp_redirect(home_url());
    exit;
}
?>

<style>
.osrg-login-form { max-width: 400px; margin: 50px auto; padding: 30px; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
.osrg-form-group { margin: 20px 0; }
.osrg-input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px; }
.osrg-button { width: 100%; background: #1877f2; color: white; padding: 12px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; }
.osrg-error { color: #d32f2f; margin: 10px 0; padding: 10px; background: #ffebee; border-radius: 5px; }
</style>

<div class="osrg-login-form">
    <h2 style="text-align: center; margin-bottom: 30px; color: #1877f2;">OSRG Social Login</h2>
    
    <?php if (isset($error)): ?>
    <div class="osrg-error"><?= esc_html($error) ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="osrg-form-group">
            <input type="text" name="username" class="osrg-input" placeholder="Username or Email" required>
        </div>
        <div class="osrg-form-group">
            <input type="password" name="password" class="osrg-input" placeholder="Password" required>
        </div>
        <button type="submit" class="osrg-button">Login</button>
    </form>
    
    <p style="text-align: center; margin-top: 20px; color: #666;">
        Use your WordPress account to login
    </p>
</div>