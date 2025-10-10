<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AI Redact Demo</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 50px; }
        input, textarea { width: 100%; padding: 10px; margin: 10px 0; box-sizing: border-box; }
        button { padding: 10px 20px; cursor: pointer; }
        .response { margin-top: 20px; padding: 15px; background: #f0f0f0; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>AI Redact Demo</h1>
    <form method="POST">
        <label for="user_input">Enter your text:</label>
        <textarea name="user_input" id="user_input" rows="4" placeholder="Type something..." required></textarea>
        <button type="submit">Submit</button>
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['user_input'])) {
        $input = trim($_POST['user_input']);
        $lower_input = strtolower($input);

        // Array of “bad” words
        $bad_words = ["nigger", "dick", "shit", "ass", "bitch", "fuck", "cunt", "pussy", "cock"];

        // Check if input contains any bad word
        $found_bad = false;
        foreach ($bad_words as $word) {
            if (strpos($lower_input, $word) !== false) {
                $found_bad = true;
                break;
            }
        }

        if ($found_bad) {
            $response = "Response refused: contains sensitive personal information (redacted).";
        } else {
            $response = "AI Response: " . htmlspecialchars($input);
        }

        echo '<div class="response">';
        echo '<strong>Input:</strong> ' . htmlspecialchars($input) . '<br>';
        echo '<strong>AI:</strong> ' . $response;
        echo '</div>';
    }
    ?>
</body>
</html>