<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    require_once 'config.php';
    init_db();
    
    $pdo = get_db();
    if (!$pdo) {
        die('Database connection failed');
    }
    
    echo "Database connected successfully<br>";
    echo "Database file: " . realpath('private_social.db') . "<br><br>";

// Check all users
$stmt = $pdo->query("SELECT * FROM users ORDER BY id");
$users = $stmt->fetchAll();

echo "All users in database:<br><br>";
foreach ($users as $user) {
    echo "ID: " . $user['id'] . "<br>";
    echo "Username: " . $user['username'] . "<br>";
    echo "Email: " . $user['email'] . "<br>";
    echo "Approved: " . $user['approved'] . "<br>";
    echo "Password hash: " . substr($user['password_hash'], 0, 20) . "...<br>";
    echo "Created: " . $user['created_at'] . "<br>";
    echo "<hr>";
}

// Check for OSRG2 user
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'OSRG2'");
$stmt->execute();
$osrg2 = $stmt->fetch();

if (!$osrg2) {
    echo "<br><strong style='color: red;'>OSRG2 user not found - recreating...</strong><br>";
    $password = password_hash('test123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, approved) VALUES (?, ?, ?, ?)");
    $stmt->execute(['OSRG2', 'test@osrg.lol', $password, 1]);
    echo "<br><strong style='color: green;'>OSRG2 test user recreated with password: test123</strong><br>";
} else {
    echo "<br><strong style='color: green;'>OSRG2 user exists - ID: " . $osrg2['id'] . "</strong><br>";
}

// Reset OSRG password to admin123
$osrg_password = password_hash('admin123', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = 'OSRG'");
$stmt->execute([$osrg_password]);
echo "<br><strong style='color: blue;'>OSRG password reset to: admin123</strong><br>";

// Reset OSRG2 password to test123
$osrg2_password = password_hash('test123', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = 'OSRG2'");
$stmt->execute([$osrg2_password]);
echo "<br><strong style='color: blue;'>OSRG2 password reset to: test123</strong><br>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
?>