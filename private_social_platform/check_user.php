<?php
require_once 'config.php';
init_db();

$pdo = get_db();

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

// Create OSRG2 test user if not exists
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = 'OSRG2'");
$stmt->execute();
$osrg2 = $stmt->fetch();

if (!$osrg2) {
    $password = password_hash('test123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, approved) VALUES (?, ?, ?, ?)");
    $stmt->execute(['OSRG2', 'test@osrg.lol', $password, 1]);
    echo "<br><strong>OSRG2 test user created with password: test123</strong><br>";
} else {
    echo "<br><strong>OSRG2 user already exists</strong><br>";
}
?>