<?php
session_start();

// Check if the user is logged in
if(isset($_SESSION['name'])){
    // Handle voice message upload
    if(isset($_FILES['voice']) && $_FILES['voice']['error'] == 0) {
        $voiceFile = 'voice_' . time() . '_' . rand(1000,9999) . '.webm';
        move_uploaded_file($_FILES['voice']['tmp_name'], $voiceFile);
        $avatarMap = [
            'cat' => '😺', 'dog' => '🐶', 'fox' => '🦊', 'panda' => '🐼',
            'alien' => '👽', 'robot' => '🤖', 'unicorn' => '🦄', 'penguin' => '🐧'
        ];
        $avatarKey = isset($_POST['avatar']) && isset($avatarMap[$_POST['avatar']]) ? $_POST['avatar'] : (isset($_SESSION['avatar']) && isset($avatarMap[$_SESSION['avatar']]) ? $_SESSION['avatar'] : 'cat');
        $avatar = $avatarMap[$avatarKey];
        $voice_message = "<div class='msgln'>".$avatar." <b class='user-name'>".$_SESSION['name']."</b> <audio controls src='".$voiceFile."'></audio><span class='chat-time'>".date("g:i A")."</span><br></div>";
        file_put_contents("log.html", $voice_message, FILE_APPEND | LOCK_EX);
        exit;
    }
    // Handle text message
    if(isset($_POST['text'])){
        $avatarMap = [
            'cat' => '😺', 'dog' => '🐶', 'fox' => '🦊', 'panda' => '🐼',
            'alien' => '👽', 'robot' => '🤖', 'unicorn' => '🦄', 'penguin' => '🐧'
        ];
        $avatarKey = isset($_POST['avatar']) && isset($avatarMap[$_POST['avatar']]) ? $_POST['avatar'] : (isset($_SESSION['avatar']) && isset($avatarMap[$_SESSION['avatar']]) ? $_SESSION['avatar'] : 'cat');
        $avatar = $avatarMap[$avatarKey];
        $text = $_POST['text'];
        $text_message = "<div class='msgln'>".$avatar." <b class='user-name'>".$_SESSION['name']."</b> ".stripslashes(htmlspecialchars($text))."<span class='chat-time'>".date("g:i A")."</span><br></div>";
        file_put_contents("log.html", $text_message, FILE_APPEND | LOCK_EX);
    }
}
?>