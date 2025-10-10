<?php
require_once 'config.php';
init_db();

if ($_POST['new_password'] ?? false) {
    $new_password = $_POST['new_password'];
    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    
    $pdo = get_db();
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = 'OSRG'");
    $stmt->execute([$password_hash]);
    
    echo "OSRG password updated to: " . htmlspecialchars($new_password);
    echo "<br><a href='settings.php'>Go to Settings</a>";
} else {
?>
<form method="POST">
    <h3>Update OSRG Password</h3>
    <input type="password" name="new_password" placeholder="New Password" required>
    <button type="submit">Update Password</button>
</form>
<?php } ?>