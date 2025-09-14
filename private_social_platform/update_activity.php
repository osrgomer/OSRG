<?php
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    $pdo = get_db();
    $stmt = $pdo->prepare("UPDATE users SET last_seen = CURRENT_TIMESTAMP WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
}
?>