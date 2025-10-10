<?php
require_once 'config.php';
init_db();

$new_password = 'OSRG123!';
$password_hash = password_hash($new_password, PASSWORD_DEFAULT);

$pdo = get_db();
$stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = 'OSRG'");
$stmt->execute([$password_hash]);

echo "OSRG password reset to: " . $new_password;
?>