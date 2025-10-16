<?php
require_once 'config.php';

echo "Session check: ";
if (isset($_SESSION['user_id'])) {
    echo "User ID: " . $_SESSION['user_id'] . ", Username: " . $_SESSION['username'];
} else {
    echo "Not logged in";
}
echo "<br>";

echo "Games directory: ";
if (is_dir('games')) {
    echo "EXISTS";
    if (is_writable('games')) {
        echo " and WRITABLE";
    } else {
        echo " but NOT WRITABLE";
    }
} else {
    echo "DOES NOT EXIST";
}
echo "<br>";

// Test creating a file
$testFile = 'games/test.json';
$testData = ['test' => 'data'];
if (file_put_contents($testFile, json_encode($testData))) {
    echo "File write: SUCCESS<br>";
    if (file_exists($testFile)) {
        echo "File exists: YES<br>";
        $content = file_get_contents($testFile);
        echo "File content: " . $content . "<br>";
        unlink($testFile); // Clean up
    }
} else {
    echo "File write: FAILED<br>";
}

echo "PHP version: " . phpversion() . "<br>";
echo "Current directory: " . getcwd() . "<br>";
?>