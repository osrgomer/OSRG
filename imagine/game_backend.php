<?php
// game_backend.php
// Receives actions, sends update to Gemini API, returns narrative

// Replace with your Gemini API key
$GEMINI_API_KEY = 'AIzaSyAAGk7e5nqt-tkDNr6kH1dkYbjd3N2a33w';
$GEMINI_API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $GEMINI_API_KEY;

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['actions'])) {
    echo json_encode(["error" => "No actions received."]);
    exit;
}

// Prepare prompt for Gemini
$prompt = "You are the Dungeon Master. Here are the player actions for this turn:\n";
foreach ($data['actions'] as $action) {
    $prompt .= $action['player'] . " (" . $action['character'] . "): " . $action['action'] . "\n";
}
$prompt .= "\nDescribe the outcome and update the story.";

// Send to Gemini API
$payload = [
    "contents" => [
        ["parts" => [["text" => $prompt]]]
    ]
];

$options = [
    'http' => [
        'method'  => 'POST',
        'header'  => "Content-Type: application/json\r\n",
        'content' => json_encode($payload)
    ]
];
$context  = stream_context_create($options);
$response = file_get_contents($GEMINI_API_URL, false, $context);

if ($response === FALSE) {
    echo json_encode(["narrative" => "Dungeon Master is silent this turn."]);
    exit;
}

$result = json_decode($response, true);
$narrative = $result['candidates'][0]['content']['parts'][0]['text'] ?? 'No response from Dungeon Master.';

echo json_encode(["narrative" => $narrative]);
