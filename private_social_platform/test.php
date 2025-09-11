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
    
    // Check users table structure
    $stmt = $pdo->query("PRAGMA table_info(users)");
    $columns = $stmt->fetchAll();
    echo "Users table columns: ";
    foreach($columns as $col) {
        echo $col['name'] . " ";
    }
    echo "<br>";
    
    // Show actual user data
    $stmt = $pdo->query("SELECT * FROM users WHERE username = 'OSRG' LIMIT 1");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        echo "OSRG user data: ";
        foreach($user as $key => $value) {
            echo "$key=$value ";
        }
        echo "<br>";
    } else {
        echo "OSRG user not found<br>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>