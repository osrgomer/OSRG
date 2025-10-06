<?php
// Get the video file path from URL
$video_file = $_GET['file'] ?? '';

// Security check - only allow files from uploads directory
if (empty($video_file) || strpos($video_file, '..') !== false) {
    http_response_code(404);
    exit('File not found');
}

$full_path = __DIR__ . '/uploads/' . basename($video_file);

// Check if file exists
if (!file_exists($full_path)) {
    http_response_code(404);
    exit('File not found');
}

// Get file info
$file_size = filesize($full_path);
$file_ext = strtolower(pathinfo($full_path, PATHINFO_EXTENSION));

// Set MIME type
$mime_types = [
    'mp4' => 'video/mp4',
    'mov' => 'video/quicktime',
    'avi' => 'video/x-msvideo'
];

$mime_type = $mime_types[$file_ext] ?? 'video/mp4';

// Handle range requests for video streaming
$range = $_SERVER['HTTP_RANGE'] ?? '';

if ($range) {
    // Parse range header
    preg_match('/bytes=(\d+)-(\d*)/', $range, $matches);
    $start = intval($matches[1]);
    $end = $matches[2] ? intval($matches[2]) : $file_size - 1;
    $length = $end - $start + 1;
    
    // Set partial content headers
    http_response_code(206);
    header('Content-Range: bytes ' . $start . '-' . $end . '/' . $file_size);
    header('Content-Length: ' . $length);
} else {
    // Full file
    $start = 0;
    $end = $file_size - 1;
    $length = $file_size;
    header('Content-Length: ' . $file_size);
}

// Set headers
header('Content-Type: ' . $mime_type);
header('Accept-Ranges: bytes');
header('Cache-Control: public, max-age=3600');

// Open and serve file
$file = fopen($full_path, 'rb');
fseek($file, $start);

$buffer = 8192;
while (!feof($file) && ($pos = ftell($file)) <= $end) {
    if ($pos + $buffer > $end) {
        $buffer = $end - $pos + 1;
    }
    echo fread($file, $buffer);
    flush();
}

fclose($file);
?>