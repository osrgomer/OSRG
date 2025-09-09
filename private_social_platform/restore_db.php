<?php
require_once 'config.php';

// Initialize the database
$pdo = init_db();

// Create OSRG admin user with a simple password
$username = 'OSRG';
$email = 'omer@osrg.lol';
$password = 'osrg123'; // You can change this after login
$password_hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, approved) VALUES (?, ?, ?, 1)");
$stmt->execute([$username, $email, $password_hash]);

echo "Database restored!\n";
echo "OSRG user created with password: osrg123\n";
echo "You can now login at login.php\n";
?>