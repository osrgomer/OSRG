<?php
header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);

$swipesFile = __DIR__ . '/swipes.json';
$swipes = file_exists($swipesFile) ? json_decode(file_get_contents($swipesFile), true) : [];

$currentUser = $input['currentUser'];
$targetUser = $input['targetUser'];
$action = $input['action'];

if (!isset($swipes[$currentUser])) {
    $swipes[$currentUser] = [];
}
$swipes[$currentUser][$targetUser] = $action;
file_put_contents($swipesFile, json_encode($swipes));
echo json_encode(['success' => true]);
?>