<?php
require_once 'config.php';

echo "<h3>Testing WordPress Authentication</h3>";

// Test database connection
$wp_pdo = get_wp_db();
if ($wp_pdo) {
    echo "✅ WordPress database connection successful<br>";
    
    // Get WordPress users
    try {
        $stmt = $wp_pdo->prepare("SELECT ID, user_login, user_email FROM wp_users LIMIT 5");
        $stmt->execute();
        $users = $stmt->fetchAll();
        
        echo "Found " . count($users) . " WordPress users:<br>";
        foreach ($users as $user) {
            echo "- {$user['user_login']} ({$user['user_email']})<br>";
        }
        
        // Test authentication with first user (you can change this)
        if (count($users) > 0) {
            echo "<br><form method='POST'>";
            echo "Test login with username: <input type='text' name='test_user' value='{$users[0]['user_login']}'><br>";
            echo "Password: <input type='password' name='test_pass'><br>";
            echo "<input type='submit' value='Test Login'>";
            echo "</form>";
            
            if ($_POST['test_user'] ?? false) {
                $result = wp_social_authenticate($_POST['test_user'], $_POST['test_pass']);
                if ($result) {
                    echo "✅ Authentication successful for: " . $result['username'];
                } else {
                    echo "❌ Authentication failed";
                }
            }
        }
        
    } catch (Exception $e) {
        echo "❌ Error querying users: " . $e->getMessage();
    }
    
} else {
    echo "❌ WordPress database connection failed";
}
?>