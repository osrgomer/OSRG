<?php
session_start();

// Initialize variables to prevent undefined variable errors if form hasn't been submitted yet
$movieScores = [];
$imageURLs = [];
$trailerURLs = [];
$indexes = [];

if(isset($_POST['submit'])) {
    // get csv file
    // Changed from Google Sheets URL to local 'movies.csv' as per the provided edit snippet
    $csvFile = fopen('movies.csv', 'r');

    //collect user data
    $userData = [
        'hour' => $_POST['hour'],
        'sex' => $_POST['sex'],
        'mood' => $_POST['mood'],
        'yourday' => $_POST['yourday']
    ];

 // collect movie scores
$movieScores = [];
$firstline = true;
$counter = 0;
while (($line = fgetcsv($csvFile)) !== false) {
    // Skip header row
    if ($firstline) {
        $firstline = false;
        continue;
    }
    
    // Skip if title is empty
    if (empty($line[0])) {
        continue;
    }
    
    // Genre filter
    $movie_genre = isset($line[19]) ? $line[19] : '';
    $selected_genre = isset($_POST['genre']) ? $_POST['genre'] : 'all';
    if ($selected_genre !== 'all' && $movie_genre !== $selected_genre) {
        continue;
    }
    
    $score = 0;
    $image_url = isset($line[17]) ? $line[17] : '';
    $trailer_url = isset($line[2]) ? $line[2] : '';
    $info_link = isset($line[18]) ? $line[18] : '';
    // $watch_link removed
    $index = $counter;
    $counter++;
    
    foreach ($userData as $key => $value) {
        if($key == 'hour') {
            if($value == 'morning' && isset($line[3])) {
                $score += floatval($line[3]);
            } else if ($value == 'noon' && isset($line[4])) {
                $score += floatval($line[4]);
            } else if ($value == 'afternoon' && isset($line[5])) {
                $score += floatval($line[5]);
            } else if ($value == 'evening' && isset($line[6])) {
                $score += floatval($line[6]);
            }
        } else if($key == 'sex') {
            if($value == 'male' && isset($line[8])) {
                $score += floatval($line[8]);
            } else if ($value == 'female' && isset($line[9])) {
                $score += floatval($line[9]);
            }
        } else if($key == 'mood') {
            if($value == 'tired' && isset($line[11])) {
                $score += floatval($line[11]);
            } else if ($value == 'energized' && isset($line[12])) {
                $score += floatval($line[12]);
            } else if ($value == 'stress' && isset($line[13])) {
                $score += floatval($line[13]);
            } else if ($value == 'happy' && isset($line[14])) {
                $score += floatval($line[14]);
            } else if ($value == 'relaxed' && isset($line[15])) {
                $score += floatval($line[15]);
            }
        } else if($key == 'yourday') {
            if($value == 'tranquil' && isset($line[21])) {
                $score += floatval($line[21]);
            } else if ($value == 'physical' && isset($line[22])) {
                $score += floatval($line[22]);
            } else if ($value == 'emotional' && isset($line[23])) {
                $score += floatval($line[23]);
            }
        } 
    }

    if ($score < 0) $score = 0;
    $movieScores[$line[0]] = $score; 
    $imageURLs[$line[0]] = $image_url;
    $trailerURLs[$line[0]] = $trailer_url;
    $indexes[$line[0]] = $index;
}
    // sort movie scores
    arsort($movieScores);


}

?>



<!DOCTYPE html>
<html>
<head>
    <title>MovieMatch</title>
<style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #121212;
            background-image: linear-gradient(to bottom, #121212, #2d2d2d);
            background-size: cover;
            background-attachment: fixed;
            color: #e0e0e0;
        }
        #main {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            background-color: #1e1e1e;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
            text-align: center;
            opacity: 0.95;
            border-radius: 8px;
        }
        h1 {
            font-size: 2.5em;
            font-weight: bold;
            color: #bb86fc;
            margin-bottom: 20px;
        }
        p {
            font-size: 1.1em;
            color: #b0b0b0;
            line-height: 1.4;
            padding: 10px;
        }
        #description, form {
            font-size: 1em;
            color: #e0e0e0;
            padding-top: 10px;
            margin-top: 1em;
        }
        .top-description { 
            font-size: 1.1em;
            color: #b0b0b0;
        }
        .bottom-description { 
            font-size: 1.1em;
            color: #b0b0b0;
        }
        .tea-thumb { 
            width: 65px;
            margin-right: 1em;
            vertical-align: middle;
            float: left;
            border-radius: 4px;
        }
        .name-mark {
            float: left;
            margin-top: 1em;
            color: #03dac6;
            font-weight: bold;
        }   
         a:link,a:visited {
            color: #03dac6;
            text-decoration: none;
        }
        a:hover {
            color: #bb86fc;
        }
        #image { 
            margin: auto;
            padding: 20px;
            max-width: 600px;
            padding-top: 20px;
        }
        #Back {
            color: #e0e0e0;
            font-size: 1.5em;
            margin-top: 20px;
        }
        #tea-info {
            font-size: 1.2em;
            color: #e0e0e0;
            padding-top: 20px;
        }
        select, input, .label {
            font-size: 1.2em;
            color: #e0e0e0;
        }
        .question {
            display: inline-block;
            width: 90%;
            margin-bottom: 15px;
            text-align: left;
        }
        #results {
            font-size: 1.2em;
            border: 1px solid #333;
            padding: 1em;
            background-color: #2c2c2c;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        form {
            border: 1px solid #333;
            padding: 20px;
            background-color: #2c2c2c;
            border-radius: 8px;
        }
        .label {
            display: block;
            margin-bottom: 5px;
            color: #bb86fc;
        }
        select {
            width: 100%;
            padding: 10px;
            background-color: #333;
            border: 1px solid #444;
            color: #e0e0e0;
            border-radius: 4px;
        }
        .send {
            margin-top: 1em;
            padding: 12px 24px;
            background-color: #bb86fc;
            color: #000;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1.1em;
            transition: background-color 0.3s;
        }
        .send:hover {
            background-color: #9965f4;
        }
        .list-line {
            display: block;
            width: 100%;
            margin-bottom: 1em;
            clear: both;
            overflow: hidden;
            padding: 10px;
            background: #333;
            border-radius: 4px;
            position: relative;
        }
        .fav-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            font-size: 1.5em;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .fav-btn:hover {
            transform: translateY(-50%) scale(1.2);
        }
        .fav-btn.favorited {
            filter: drop-shadow(0 0 3px #ff6b6b);
        }
        .results-list {
            margin-bottom: 1em;
        }
        #youtube-video-player {
            background: #000;
            border-radius: 8px;
            overflow: hidden;
        }
        @media only screen and (max-width: 600px) {
            #main {
                width: 100%;
                padding: 10px;
            }
            h1 {
                font-size: 2em;
            }
        }
    </style>
    <script>
    // Favorites management
    function toggleFavorite(movie) {
        let favorites = JSON.parse(localStorage.getItem('movieFavorites') || '[]');
        const index = favorites.findIndex(f => f.title === movie.title);
        
        if (index > -1) {
            favorites.splice(index, 1);
        } else {
            favorites.push(movie);
        }
        
        localStorage.setItem('movieFavorites', JSON.stringify(favorites));
        updateFavoriteButtons();
    }
    
    function updateFavoriteButtons() {
        const favorites = JSON.parse(localStorage.getItem('movieFavorites') || '[]');
        document.querySelectorAll('.fav-btn').forEach(btn => {
            const title = btn.getAttribute('data-title');
            if (favorites.some(f => f.title === title)) {
                btn.classList.add('favorited');
            } else {
                btn.classList.remove('favorited');
            }
        });
    }
    
    window.addEventListener('DOMContentLoaded', updateFavoriteButtons);
    </script>
</head>
<body>
    <div id="main">
        <h1>Welcome to MovieMatch: Your Trailer Advisor!</h1>
        <div style="text-align: center; margin-bottom: 20px;">
            <a href="favorites.php" style="color: #bb86fc; text-decoration: none; font-size: 1.2em; font-weight: bold;">❤️ My Favorites</a>
        </div>
        <?php if(isset($_POST['submit'])) { ?>
        


        <div id="results">
        <p>Here are the best options for you:</p>
            <div class="results-list">
            <?php
                // present movie scores to the user
                $count = 1;
                foreach($movieScores as $title => $score) {
                    $im_url = $imageURLs[$title];
                    $url = $trailerURLs[$title];
                    $index = $indexes[$title];
                    
                    if (($count<=3)&&($title!=='title')) {
                        $movie_data = htmlspecialchars(json_encode(['title' => $title, 'index' => $index, 'score' => $score]));
                        echo "<div class='list-line'>
                                <img class='tea-thumb' src='" . $im_url . "' /> 
                                <span class='name-mark'>" . $title . " (" . $score . "%)</span> 
                                <a href='trailer-page.php?id=" . $index . "'>Watch Trailer</a>
                                <button class='fav-btn' onclick='toggleFavorite(" . $movie_data . ")' data-title='" . htmlspecialchars($title) . "'>❤️</button>
                              </div>";
                        $count = $count + 1;
                    }
                }
                ?>
            </div>
        </div>
        
        <div style="margin-top: 20px;">
            <a href="index.php" class="send" style="display: inline-block; text-decoration: none; text-align: center;">Try Again</a>
        </div>
        
        <?php
        }
        ?>
        
        
        <div id="description">
            <p class="top-description">Are you ready for a movie night but can't decide what to watch? Look no further! Let MovieMatch help you find the perfect movie trailer based on your current mood and preferences. Whether you're winding down after a long day or looking for some weekend entertainment, we've got you covered.</p>
        </div>
        <form action="" method="post">
            <div class="question">
                <div class="label">What's the time?</div>
                <select name="hour">
                    <option value="morning" <?php if (isset($_POST['hour']) && $_POST['hour'] == 'morning') echo 'selected="selected"'; ?>>morning</option>
                    <option value="noon" <?php if (isset($_POST['hour']) && $_POST['hour'] == 'noon') echo 'selected="selected"'; ?>>noon</option>
                    <option value="afternoon" <?php if (isset($_POST['hour']) && $_POST['hour'] == 'afternoon') echo 'selected="selected"'; ?>>afternoon</option>
                    <option value="evening" <?php if (isset($_POST['hour']) && $_POST['hour'] == 'evening') echo 'selected="selected"'; ?>>evening</option>
                </select>
            </div>
            <br>
            <div class="question">
                <div class="label">Your gender?</div>
                <select name="sex">
                    <option value="male" <?php if (isset($_POST['sex']) && $_POST['sex'] == 'male') echo 'selected="selected"'; ?>>male</option>
                    <option value="female" <?php if (isset($_POST['sex']) && $_POST['sex'] == 'female') echo 'selected="selected"'; ?>>female</option>
                    <option value="none" <?php if (isset($_POST['sex']) && $_POST['sex'] == 'none') echo 'selected="selected"'; ?>>not relevant</option>
                </select>
            </div>
            <br>
            <div class="question">
                <div class="label">Your mood?</div>
                <select name="mood">
                    <option value="tired" <?php if (isset($_POST['mood']) && $_POST['mood'] == 'tired') echo 'selected="selected"'; ?>>tired</option>
                    <option value="energized" <?php if (isset($_POST['mood']) && $_POST['mood'] == 'energized') echo 'selected="selected"'; ?>>energized</option>
                    <option value="stress" <?php if (isset($_POST['mood']) && $_POST['mood'] == 'stress') echo 'selected="selected"'; ?>>stress</option>
                    <option value="happy" <?php if (isset($_POST['mood']) && $_POST['mood'] == 'happy') echo 'selected="selected"'; ?>>happy</option>
                    <option value="relaxed" <?php if (isset($_POST['mood']) && $_POST['mood'] == 'relaxed') echo 'selected="selected"'; ?>>relaxed</option>
                </select>
            </div>
            <br>
            <div class="question">
                <div class="label">How is your day?</div> 
                <select name="yourday">
                    <option value="tranquil" <?php if (isset($_POST['yourday']) && $_POST['yourday'] == 'tranquil') echo 'selected="selected"'; ?>>tranquil</option>
                    <option value="physical" <?php if (isset($_POST['yourday']) && $_POST['yourday'] == 'physical') echo 'selected="selected"'; ?>>physical</option>
                    <option value="emotional" <?php if (isset($_POST['yourday']) && $_POST['yourday'] == 'emotional') echo 'selected="selected"'; ?>>emotional</option>
                </select>
            </div>
            <br>
            <div class="question">
                <div class="label">Genre preference?</div> 
                <select name="genre">
                    <option value="all" <?php if (isset($_POST['genre']) && $_POST['genre'] == 'all') echo 'selected="selected"'; ?>>All Genres</option>
                    <option value="Action" <?php if (isset($_POST['genre']) && $_POST['genre'] == 'Action') echo 'selected="selected"'; ?>>Action</option>
                    <option value="Action Comedy" <?php if (isset($_POST['genre']) && $_POST['genre'] == 'Action Comedy') echo 'selected="selected"'; ?>>Action Comedy</option>
                    <option value="Comedy" <?php if (isset($_POST['genre']) && $_POST['genre'] == 'Comedy') echo 'selected="selected"'; ?>>Comedy</option>
                    <option value="Fantasy Adventure" <?php if (isset($_POST['genre']) && $_POST['genre'] == 'Fantasy Adventure') echo 'selected="selected"'; ?>>Fantasy Adventure</option>
                    <option value="Sci-Fi Action" <?php if (isset($_POST['genre']) && $_POST['genre'] == 'Sci-Fi Action') echo 'selected="selected"'; ?>>Sci-Fi Action</option>
                    <option value="Romance" <?php if (isset($_POST['genre']) && $_POST['genre'] == 'Romance') echo 'selected="selected"'; ?>>Romance</option>
                </select>
            </div>
            <br>
            <input class="send" type="submit" name="submit" value="Recommend a trailer">
        </form>
        <div id="tea-info">
            <p class="bottom-description" ></p>
            <h1 id="Back" ><a href="/">Back</a></h1>
        </div>
    </div>
</body>
</html>