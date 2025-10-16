<?php
session_start();
header('Content-Type: application/json');

// Simple test response
echo json_encode([
    'success' => true,
    'differences' => [
        ['x' => 100, 'y' => 100, 'radius' => 25, 'found' => false, 'color' => 'hsl(0, 70%, 50%)'],
        ['x' => 200, 'y' => 150, 'radius' => 25, 'found' => false, 'color' => 'hsl(60, 70%, 50%)'],
        ['x' => 300, 'y' => 200, 'radius' => 25, 'found' => false, 'color' => 'hsl(120, 70%, 50%)'],
        ['x' => 400, 'y' => 250, 'radius' => 25, 'found' => false, 'color' => 'hsl(180, 70%, 50%)'],
        ['x' => 500, 'y' => 300, 'radius' => 25, 'found' => false, 'color' => 'hsl(240, 70%, 50%)']
    ],
    'players' => [
        $_SESSION['user_id'] ?? '1' => [
            'username' => $_SESSION['username'] ?? 'Player',
            'score' => 0
        ]
    ],
    'message' => 'Game loaded successfully!',
    'messageType' => 'waiting'
]);
?>