<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
        exit;
    }
    
    $action = $input['action'] ?? '';
    $room = $input['room'] ?? '';
    
    if (!$action) {
        echo json_encode(['success' => false, 'error' => 'No action specified']);
        exit;
    }

function generateDifferences() {
    $diffs = [];
    for ($i = 0; $i < 5; $i++) {
        $diffs[] = [
            'x' => rand(50, 550),
            'y' => rand(50, 350),
            'radius' => 25,
            'found' => false,
            'color' => sprintf("hsl(%d, 70%%, 50%%)", rand(0, 360))
        ];
    }
    return $diffs;
}

    // Simple file-based storage
    $gameFile = 'games/' . preg_replace('/[^a-zA-Z0-9]/', '', $room) . '.json';
    
    if (!is_dir('games')) {
        if (!mkdir('games', 0755, true)) {
            echo json_encode(['success' => false, 'error' => 'Cannot create games directory']);
            exit;
        }
    }

switch ($action) {
    case 'join':
        if (!$room) {
            echo json_encode(['success' => false, 'error' => 'Room name required']);
            exit;
        }
        
        $gameData = [];
        if (file_exists($gameFile)) {
            $gameData = json_decode(file_get_contents($gameFile), true) ?: [];
        } else {
            $gameData = [
                'differences' => generateDifferences(),
                'players' => []
            ];
        }
        
        $gameData['players'][$_SESSION['user_id']] = [
            'username' => $_SESSION['username'],
            'score' => 0,
            'lastActive' => time()
        ];
        
        if (file_put_contents($gameFile, json_encode($gameData)) === false) {
            echo json_encode(['success' => false, 'error' => 'Cannot save game data']);
            exit;
        }
        
        echo json_encode([
            'success' => true,
            'differences' => $gameData['differences'],
            'players' => $gameData['players'],
            'message' => "Joined room: $room",
            'messageType' => 'waiting'
        ]);
        break;
        
    case 'newgame':
        $gameData = [
            'differences' => generateDifferences(),
            'players' => []
        ];
        
        if (file_exists($gameFile)) {
            $oldData = json_decode(file_get_contents($gameFile), true) ?: [];
            if (isset($oldData['players'])) {
                foreach ($oldData['players'] as $id => $player) {
                    $player['score'] = 0;
                    $gameData['players'][$id] = $player;
                }
            }
        }
        
        if (file_put_contents($gameFile, json_encode($gameData)) === false) {
            echo json_encode(['success' => false, 'error' => 'Cannot save game data']);
            exit;
        }
        
        echo json_encode([
            'success' => true,
            'differences' => $gameData['differences'],
            'players' => $gameData['players'],
            'message' => 'New game started!',
            'messageType' => 'waiting'
        ]);
        break;
        
    case 'found':
        if (!file_exists($gameFile)) {
            echo json_encode(['success' => false, 'error' => 'Game not found']);
            exit;
        }
        
        $gameData = json_decode(file_get_contents($gameFile), true);
        $index = (int)$input['index'];
        
        if (isset($gameData['differences'][$index]) && !$gameData['differences'][$index]['found']) {
            $gameData['differences'][$index]['found'] = true;
            $gameData['players'][$_SESSION['user_id']]['score'] += 10;
            
            if (file_put_contents($gameFile, json_encode($gameData)) === false) {
                echo json_encode(['success' => false, 'error' => 'Cannot save game data']);
                exit;
            }
            
            echo json_encode([
                'success' => true,
                'differences' => $gameData['differences'],
                'players' => $gameData['players'],
                'message' => 'You found a difference! +10 points',
                'messageType' => 'found'
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Already found']);
        }
        break;
        
    case 'update':
        if (file_exists($gameFile)) {
            $gameData = json_decode(file_get_contents($gameFile), true);
            echo json_encode([
                'success' => true,
                'differences' => $gameData['differences'] ?? [],
                'players' => $gameData['players'] ?? []
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Game not found']);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}
?>