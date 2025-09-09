<?php
// Test WordPress authentication
try {
    require_once('wp-config.php');
    require_once('wp-includes/wp-db.php');
    require_once('wp-includes/pluggable.php');
    require_once('wp-includes/user.php');
    
    echo "WordPress files loaded successfully<br>";
    
    // Test database connection
    global $wpdb;
    $users = $wpdb->get_results("SELECT ID, user_login, user_email FROM {$wpdb->users} LIMIT 5");
    echo "Found " . count($users) . " WordPress users:<br>";
    foreach ($users as $user) {
        echo "- {$user->user_login} ({$user->user_email})<br>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>