<?php
// Standalone WordPress test - no redirects
error_reporting(E_ALL);
ini_set('display_errors', 1);

// WordPress database configuration
$wp_db_host = 'localhost';
$wp_db_name = 'u542077544_OSRGConnect';
$wp_db_user = 'u542077544_Omer';
$wp_db_pass = 'V0Zw7celP]AO9';
$wp_table_prefix = 'wp_';

echo "<h2>WordPress Database Test</h2>";

try {
    $pdo = new PDO("mysql:host=$wp_db_host;dbname=$wp_db_name", $wp_db_user, $wp_db_pass);
    echo "✅ Database connection successful<br><br>";
    
    // Get WordPress users
    $stmt = $pdo->prepare("SELECT ID, user_login, user_email FROM {$wp_table_prefix}users LIMIT 10");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    echo "<strong>WordPress Users Found:</strong><br>";
    foreach ($users as $user) {
        echo "ID: {$user['ID']} - Username: {$user['user_login']} - Email: {$user['user_email']}<br>";
    }
    
    echo "<br><strong>Try logging in with any of these usernames and your WordPress password at:</strong><br>";
    echo "<a href='login.php'>login.php</a>";
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage();
}
?>