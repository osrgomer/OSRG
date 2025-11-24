<?php

if(isset($_POST['submit'])) {
    // get csv file
    $csvFile = fopen('https://docs.google.com/spreadsheets/d/e/2PACX-1vRTzKstpqvyi9uBR6A2X86sYJwVbl4NtuerO3-LG7Wgou209GV8KsNiZv8aSZ1la_FddB7RqilW49cV/pub?output=csv', 'r');

    //collect user data
    $userData = [
        'hour' => $_POST['hour'],
        'sex' => $_POST['sex'],
        'mood' => $_POST['mood'],
        'yourday' => $_POST['yourday']
    ];

 // collect tea scores
$teaScores = [];
$firstline = true;
$counter = 0;
while (($line = fgetcsv($csvFile)) !== false) {
    $score = 0;
    $image_url = $line[29];
    $trailer_url = $line[2];
    $index = $counter;
    $counter ++;
        foreach ($userData as $key => $value) {
            if($key == 'hour') {
                if($value == 'morning') {
                    $score += $line[3];
                } else if ($value == 'noon') {
                    $score += $line[4];
                } else if ($value == 'afternoon') {
                    $score += $line[5];
                } else if ($value == 'evening') {
                    $score += $line[6];
                }
            } else if($key == 'sex') {
                if($value == 'male') {
                    $score += $line[8];
                } else if ($value == 'female') {
                    $score += $line[9];
                }
            } else if($key == 'mood') {
                if($value == 'tired') {
                    $score += $line[11];
                } else if ($value == 'energized') {
                    $score += $line[12];
                } else if ($value == 'stress') {
                    $score += $line[13];
                } else if ($value == 'happy') {
                    $score += $line[14];
                } else if ($value == 'relaxed') {
                    $score += $line[15];
                }
            } else if($key == 'yourday') {
                if($value == 'tranquil') {
                    $score += $line[21];
                } else if ($value == 'physical') {
                    $score += $line[22];
                } else if ($value == 'emotional') {
                    $score += $line[23];
                }
            } 
    }

    if ($score<0) $score = 0;
    $teaScores[$line[0]] = $score; 
    $imageURLs[$line[0]] = $image_url;
    $trailerURLs[$line[0]] = $trailer_url;
    $indexes[$line[0]] = $index;
}
    // sort tea scores
    arsort($teaScores);


}

?>



<!DOCTYPE html>
<html>
<head>
    <title>MovieMatch</title>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery-tubeplayer/2.1.0/jquery.tubeplayer.min.js"></script>
<div id='youtube-video-player'></div>
<script type="text/javascript">
jQuery(document).ready(function(){
    jQuery("#youtube-video-player").tubeplayer({
    });
});
</script>
<style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: sans-serif;
            background-color: #000000;
            background-image: url('images/tea.jpg');
            background-size: cover;
        }
        #main {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            background-color: #FFFFFF;
            box-shadow: 10px 10px 10px rgba(0, 0, 0, 0.5);
            text-align: center;
            opacity: 80%;
        }
        h1 {
            font-size: 2.5em;
            font-weight: bold;
            color: #863eb3;
        }
        p {
            font-size: 1.2em;
            color: #2D2D2D;
            line-height: 1.1;
            padding: 10px;
        }
        #description, form {
            font-size: 0.6em;
            color: #2D2D2D;
            padding-top: 10px;
            margin-top: 1em;
        }
        .top-description { 
            font-size: 25px;
            color: #2D2D2D;
        }
        .bottom-description { 
            font-size: 25px;
            color: #2D2D2D;
        }
        .tea-thumb { 
            width: 65px;
            margin-right: 1em;
            vertical-align: middle;
            float: left;
        }
        .name-mark {
            float: left;
            margin-top: 1em;
        }   
         a:link,a:visited {
            color: #000000;
        }
        #image { 
            margin: auto;
            padding: 20px;
            max-width: 600px;
            padding-top: 20px;
        }
        #Back {
            color: #2D2D2D;
            font-size: 2em
        }
        #tea-info {
            font-size: 1.2em;
            color: #2D2D2D;
            padding-top: 20px;
        }
        select, input, .label {
            font-size: 2em;
        }
        .question {
            display: inline-block;
            width: 80%;
        }
        #results {
            font-size: 1.5em;
        }
        form, #results {
            border-style: solid;
            padding: 1em;
        }
        .label {
            float: left;
        }
        select {
            float: right;
            min-width: 50%;
        }
        .send {
            margin-top: 1em;
            padding: 0.8em;
            background-color: green;
            color: white;
        }
        .list-line {
            display: inline-block;
            width: 56%;
            margin-bottom: 1em;
        }
        .results-list {
            margin-bottom: 1em;
        }
        @media only screen and (max-width: 1000px) {
            #main {
                width: 100%;
                font-size: 28px;
            }
            select, input, .label {
                font-size: 4em;
            }
            select, input {
                margin-bottom: 0.5em;
            }

        }
    </style>
</head>
<body>
    <div id="main">
        <h1>Welcome to MovieMatch: Your Trailer Advisor!</h1>
        <?php if(isset($_POST['submit'])) { ?>

        <div id="results">
        <p>Here are the best options for you:</p>
            <div class="results-list">
            <?php
                // present tea scores to the user
                $count = 1;
                foreach($teaScores as $title => $score) {
                    $im_url = $imageURLs[$title];
                    $url = $trailerURLs[$title];
                    $index = $indexes[$title];
                    
                    if (($count<=3)&&($title!=='title')) {
                        echo "<div class='list-line'><img class='tea-thumb' src='" . $im_url . "' /> <span class='name-mark'>" . $title . ', ' . $url .': ' . $score . '</span></div>';
                        $count = $count + 1;
                    }
                }
                ?>
            </div>
        </div>
        <script>
        <?php
                $count = 1;
            foreach($teaScores as $title => $score) {
                if ($count<3) {
                echo 'jQuery("#player").tubeplayer("play", {id: "' . $trailerURLs[$title] . '", time: 0});';
                }
                $count++;
            }
?>
            

        </script>
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
            <input class="send" type="submit" name="submit" value="Recommend a trailer">
        </form>
        <div id="tea-info">
            <p class="bottom-description" ></p>
            <h1 id="Back" ><a href="/">Back</a></h1>
        </div>
    </div>
</body>
</html>