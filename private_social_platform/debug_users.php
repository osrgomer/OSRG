<?php
require_once 'config.php';
init_db();

try {
    $pdo = get_db();
    $stmt = $pdo->query("SELECT * FROM users LIMIT 5");
    $users = $stmt->fetchAll();
    
    echo "<h3>Users in database:</h3>";
    foreach ($users as $user) {
        echo "<p>";
        echo "Username: " . htmlspecialchars($user['username']) . "<br>";
        echo "Email: " . htmlspecialchars($user['email']) . "<br>";
        echo "Approved: " . $user['approved'] . "<br>";
        echo "Has password field: " . (isset($user['password']) ? 'Yes' : 'No') . "<br>";
        echo "Has password_hash field: " . (isset($user['password_hash']) ? 'Yes' : 'No') . "<br>";
        if (isset($user['password_hash'])) {
            echo "Password hash starts with: " . substr($user['password_hash'], 0, 10) . "...<br>";
        }
        echo "</p><hr>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>