<?php
require_once 'config.php';
$pdo = get_db();

// Fix Post ID 12 again
$stmt = $pdo->prepare("UPDATE posts SET post_type = 'reel', reel_serial = 1 WHERE id = 12");
$stmt->execute();

echo "✅ Fixed Post ID 12 as Reel #1";
?>