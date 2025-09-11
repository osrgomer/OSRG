<?php
echo "PHP is working<br>";

try {
    $pdo = new PDO('sqlite:private_social.db');
    echo "SQLite connection works<br>";
    
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'");
    $tables = $stmt->fetchAll();
    echo "Tables: ";
    foreach($tables as $table) {
        echo $table['name'] . " ";
    }
    echo "<br>";
    
    $stmt = $pdo->query("SELECT username FROM users LIMIT 1");
    $user = $stmt->fetch();
    echo "First user: " . ($user ? $user['username'] : 'none') . "<br>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>