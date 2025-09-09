<?php
// Quick WordPress debug - bypass all redirects
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head><title>WordPress Debug</title></head>
<body>
<h2>WordPress Database Test</h2>
<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=u542077544_OSRGConnect", "u542077544_Omer", "V0Zw7celP]AO9");
    echo "✅ Database connection successful<br><br>";
    
    $stmt = $pdo->prepare("SELECT ID, user_login, user_email FROM wp_users LIMIT 10");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    echo "<strong>WordPress Users (" . count($users) . "):</strong><br>";
    foreach ($users as $user) {
        echo "Username: <strong>{$user['user_login']}</strong><br>";
    }
    
    echo "<br>✅ Use any username above with your WordPress password at login.php";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
</body>
</html>