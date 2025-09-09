<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if user is already logged into WordPress
if (is_user_logged_in()) {
    return '<p>You are already logged in! <a href="' . wp_logout_url() . '">Logout</a></p>';
}

// Handle login
if ($_POST['username'] ?? false) {
    $creds = array(
        'user_login'    => $_POST['username'],
        'user_password' => $_POST['password'],
        'remember'      => true
    );
    
    $user = wp_signon($creds, false);
    
    if (!is_wp_error($user)) {
        wp_redirect(home_url());
        exit;
    } else {
        $error = 'Invalid username or password';
    }
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