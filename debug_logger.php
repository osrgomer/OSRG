<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if ($data && isset($data['message']) && isset($data['type'])) {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [{$data['type']}] {$data['message']}\n";
        
        $logFile = 'debug_' . $data['type'] . '.log';
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
        echo json_encode(['status' => 'logged']);
    }
}
?>