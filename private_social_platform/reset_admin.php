<?php
require_once 'config.php';
init_db();

$pdo = get_db();

// Reset OSRG password to admin123
$password = password_hash('admin123', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'OSRG'");
$stmt->execute([$password]);

echo "OSRG password reset to: admin123";
?>