<?php
require_once 'config.php';
init_db();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
$room = $input['room'] ?? '';

function generateDifferences() {
    $diffs = [];
    for ($i = 0; $i < 7; $i++) {
        $diffs[] = [
            'x' => rand(20, 580),
            'y' => rand(20, 380),
            'radius' => 25,
            'found' => false,
            'color' => sprintf("hsl(%d, 70%%, 50%%)", rand(0, 360))
        ];
    }
    return $diffs;
}

function cleanupOldPlayers($room) {
    global $pdo;
    // Remove players inactive for more than 30 seconds
    $stmt = $pdo->prepare("DELETE FROM game_players WHERE room_name = ? AND last_active < datetime('now', '-30 seconds')");
    $stmt->execute([$room]);
}

function getGameData($room) {
    global $pdo;
    
    cleanupOldPlayers($room);
    
    // Get game data
    $stmt = $pdo->prepare("SELECT game_data FROM multiplayer_games WHERE room_name = ?");
    $stmt->execute([$room]);
    $game = $stmt->fetch();
    
    // Get players
    $stmt = $pdo->prepare("SELECT user_id, username, score FROM game_players WHERE room_name = ?");
    $stmt->execute([$room]);
    $players = [];
    while ($player = $stmt->fetch()) {
        $players[$player['user_id']] = [
            'username' => $player['username'],
            'score' => $player['score']
        ];
    }
    
    $differences = $game ? json_decode($game['game_data'], true) : [];
    
    return [
        'differences' => $differences,
        'players' => $players
    ];
}

switch ($action) {
    case 'join':
        if (!$room) {
            echo json_encode(['success' => false, 'error' => 'Room name required']);
            exit;
        }
        
        // Create game if doesn't exist
        $stmt = $pdo->prepare("SELECT id FROM multiplayer_games WHERE room_name = ?");
        $stmt->execute([$room]);
        if (!$stmt->fetch()) {
            $differences = generateDifferences();
            $stmt = $pdo->prepare("INSERT INTO multiplayer_games (room_name, game_data) VALUES (?, ?)");
            $stmt->execute([$room, json_encode($differences)]);
        }
        
        // Add/update player
        $stmt = $pdo->prepare("INSERT OR REPLACE INTO game_players (room_name, user_id, username, score, last_active) VALUES (?, ?, ?, 0, datetime('now'))");
        $stmt->execute([$room, $_SESSION['user_id'], $_SESSION['username']]);
        
        $gameData = getGameData($room);
        echo json_encode([
            'success' => true,
            'differences' => $gameData['differences'],
            'players' => $gameData['players'],
            'message' => "Joined room: $room",
            'messageType' => 'waiting'
        ]);
        break;
        
    case 'leave':
        if ($room) {
            $stmt = $pdo->prepare("DELETE FROM game_players WHERE room_name = ? AND user_id = ?");
            $stmt->execute([$room, $_SESSION['user_id']]);
        }
        echo json_encode(['success' => true]);
        break;
        
    case 'newgame':
        if (!$room) {
            echo json_encode(['success' => false, 'error' => 'Room name required']);
            exit;
        }
        
        // Reset scores
        $stmt = $pdo->prepare("UPDATE game_players SET score = 0 WHERE room_name = ?");
        $stmt->execute([$room]);
        
        // Generate new differences
        $differences = generateDifferences();
        $stmt = $pdo->prepare("UPDATE multiplayer_games SET game_data = ?, updated_at = datetime('now') WHERE room_name = ?");
        $stmt->execute([json_encode($differences), $room]);
        
        $gameData = getGameData($room);
        echo json_encode([
            'success' => true,
            'differences' => $gameData['differences'],
            'players' => $gameData['players'],
            'message' => 'New game started!',
            'messageType' => 'waiting'
        ]);
        break;
        
    case 'found':
        if (!$room || !isset($input['index'])) {
            echo json_encode(['success' => false, 'error' => 'Invalid request']);
            exit;
        }
        
        $index = (int)$input['index'];
        
        // Get current game data
        $stmt = $pdo->prepare("SELECT game_data FROM multiplayer_games WHERE room_name = ?");
        $stmt->execute([$room]);
        $game = $stmt->fetch();
        
        if ($game) {
            $differences = json_decode($game['game_data'], true);
            
            if (isset($differences[$index]) && !$differences[$index]['found']) {
                // Mark as found
                $differences[$index]['found'] = true;
                $differences[$index]['foundBy'] = $_SESSION['user_id'];
                
                // Update game data
                $stmt = $pdo->prepare("UPDATE multiplayer_games SET game_data = ?, updated_at = datetime('now') WHERE room_name = ?");
                $stmt->execute([json_encode($differences), $room]);
                
                // Update player score
                $stmt = $pdo->prepare("UPDATE game_players SET score = score + 10, last_active = datetime('now') WHERE room_name = ? AND user_id = ?");
                $stmt->execute([$room, $_SESSION['user_id']]);
                
                $gameData = getGameData($room);
                
                // Check if game is complete
                $allFound = true;
                foreach ($differences as $diff) {
                    if (!$diff['found']) {
                        $allFound = false;
                        break;
                    }
                }
                
                $message = 'You found a difference! +10 points';
                $messageType = 'found';
                
                if ($allFound) {
                    // Find winner
                    $maxScore = 0;
                    $winner = '';
                    foreach ($gameData['players'] as $player) {
                        if ($player['score'] > $maxScore) {
                            $maxScore = $player['score'];
                            $winner = $player['username'];
                        }
                    }
                    $message = "Game Over! Winner: $winner with $maxScore points!";
                    $messageType = 'found';
                }
                
                echo json_encode([
                    'success' => true,
                    'differences' => $gameData['differences'],
                    'players' => $gameData['players'],
                    'message' => $message,
                    'messageType' => $messageType
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Already found or invalid']);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Game not found']);
        }
        break;
        
    case 'update':
        if (!$room) {
            echo json_encode(['success' => false, 'error' => 'Room name required']);
            exit;
        }
        
        // Update player activity
        $stmt = $pdo->prepare("UPDATE game_players SET last_active = datetime('now') WHERE room_name = ? AND user_id = ?");
        $stmt->execute([$room, $_SESSION['user_id']]);
        
        $gameData = getGameData($room);
        echo json_encode([
            'success' => true,
            'differences' => $gameData['differences'],
            'players' => $gameData['players']
        ]);
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}
?>