<?php
require_once 'config.php';
init_db();

$pdo = get_db();
$stmt = $pdo->query("SELECT username, email, approved, password, password_hash FROM users LIMIT 5");
$users = $stmt->fetchAll();

echo "<h3>Users in database:</h3>";
foreach ($users as $user) {
    echo "<p>";
    echo "Username: " . htmlspecialchars($user['username']) . "<br>";
    echo "Email: " . htmlspecialchars($user['email']) . "<br>";
    echo "Approved: " . $user['approved'] . "<br>";
    echo "Has password field: " . (isset($user['password']) ? 'Yes' : 'No') . "<br>";
    echo "Has password_hash field: " . (isset($user['password_hash']) ? 'Yes' : 'No') . "<br>";
    echo "</p><hr>";
}
?>