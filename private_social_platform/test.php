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
    
    $stmt = $pdo->query("SELECT username FROM users WHERE username = 'OSRG'");
    $user = $stmt->fetch();
    echo "OSRG user: " . ($user ? $user['username'] : 'not found') . "<br>";
    
    if ($user) {
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'OSRG'");
        $stmt->execute([$password]);
        echo "Password reset to: admin123<br>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>