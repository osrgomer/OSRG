<?php
// WordPress Database Test - Outside social platform directory
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>WordPress Database Test</h2>";

try {
    $pdo = new PDO("mysql:host=localhost;dbname=u542077544_OSRGConnect", "u542077544_Omer", "V0Zw7celP]AO9");
    echo "✅ Database connection successful<br><br>";
    
    // Get WordPress users
    $stmt = $pdo->prepare("SELECT ID, user_login, user_email FROM wp_users LIMIT 10");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    echo "<strong>WordPress Users Found (" . count($users) . "):</strong><br>";
    foreach ($users as $user) {
        echo "Username: <strong>{$user['user_login']}</strong> - Email: {$user['user_email']}<br>";
    }
    
    echo "<br><strong>✅ WordPress integration is working!</strong><br>";
    echo "Use any username above with your WordPress password to login.<br>";
    echo "<a href='osrg/private_social_platform/login.php'>Go to Social Network Login</a>";
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage();
}
?>