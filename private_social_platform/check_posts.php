<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$pdo = get_db();

// Get total post count and latest post
$stmt = $pdo->query("SELECT COUNT(*) as count FROM posts");
$count = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT p.content, u.username FROM posts p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC LIMIT 1");
$latest = $stmt->fetch();

$response = [
    'count' => (int)$count,
    'latest_post' => $latest ? $latest['username'] . ': ' . substr($latest['content'], 0, 50) . '...' : ''
];

header('Content-Type: application/json');
echo json_encode($response);
?>