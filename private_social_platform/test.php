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
    
    $stmt = $pdo->query("SELECT * FROM users WHERE username = 'OSRG'");
    $user = $stmt->fetch();
    if ($user) {
        echo "OSRG user found<br>";
        // Try to update with correct column name
        if (isset($user['password'])) {
            $password = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'OSRG'");
            $stmt->execute([$password]);
            echo "Password reset to: admin123<br>";
        } else {
            echo "No password column found<br>";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>