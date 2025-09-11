<?php
require_once 'config.php';
init_db();

$pdo = get_db();

// Check OSRG2 user
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'OSRG2'");
$stmt->execute();
$user = $stmt->fetch();

if ($user) {
    echo "OSRG2 user found:<br>";
    echo "ID: " . $user['id'] . "<br>";
    echo "Username: " . $user['username'] . "<br>";
    echo "Email: " . $user['email'] . "<br>";
    echo "Approved: " . $user['approved'] . "<br>";
    echo "Password hash: " . substr($user['password_hash'], 0, 20) . "...<br>";
    
    // Reset password to 'test123'
    $new_password = password_hash('test123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = 'OSRG2'");
    $stmt->execute([$new_password]);
    echo "<br>Password reset to: test123<br>";
} else {
    echo "OSRG2 user not found";
}
?>