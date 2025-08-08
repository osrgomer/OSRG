<?php
header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);

$usersFile = __DIR__ . '/users.json';
$swipesFile = __DIR__ . '/swipes.json';
$users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];
$swipes = file_exists($swipesFile) ? json_decode(file_get_contents($swipesFile), true) : [];

$currentUser = $input['currentUser'];
$alreadySwiped = [];
if (isset($swipes[$currentUser])) {
    foreach ($swipes[$currentUser] as $target => $action) {
        $alreadySwiped[] = $target;
    }
}

$profiles = [];
foreach ($users as $username => $data) {
    if ($username !== $currentUser && !in_array($username, $alreadySwiped)) {
        $profiles[] = [
            'username' => $username,
            'bio' => $data['bio'],
            'image' => isset($data['image']) ? $data['image'] : ''
        ];
    }
}
echo json_encode($profiles);
?>