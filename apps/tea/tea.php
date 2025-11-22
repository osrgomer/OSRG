<?php

if(isset($_POST['submit'])) {
    // get csv file
    $csvUrl = 'https://docs.google.com/spreadsheets/d/1CCP5B4_VxVF6SoT-DPVb77NmdWXa3xCIF92pR7KAmSY/export?format=csv&gid=0';
    $csvFile = fopen($csvUrl, 'r');
    
    if (!$csvFile) {
        die('Error: Unable to access CSV data. Please try again later.');
    }

    //collect user data
    $userData = [
        'hour' => $_POST['hour'],
        'wakeup' => $_POST['wakeup'],
        'sex' => $_POST['sex'],
        'caffeine' => $_POST['caffeine'],
        'mood' => $_POST['mood'],
        'pain' => $_POST['pain'],
        'yourday' => $_POST['yourday'],
        'weather' => $_POST['weather']
    ];

 // collect tea scores
$teaScores = [];
$firstline = true;
$counter = 0;
while (($line = fgetcsv($csvFile)) !== false) {
    if (count($line) < 30) continue; // Skip incomplete rows
    $score = 0;
    $image_url = isset($line[29]) ? $line[29] : '';
    $index = $counter;
    $counter ++;
        foreach ($userData as $key => $value) {
            if($key == 'hour') {
                if($value == 'morning' && isset($line[3])) {
                    $score += (int)$line[3];
                } else if ($value == 'noon' && isset($line[4])) {
                    $score += (int)$line[4];
                } else if ($value == 'afternoon' && isset($line[5])) {
                    $score += (int)$line[5];
                } else if ($value == 'evening' && isset($line[6])) {
                    $score += (int)$line[6];
                }
            } else if($key == 'wakeup') {
                if($value == '1') {
                    $score += $line[7];
                }
            } else if($key == 'sex') {
                if($value == 'male') {
                    $score += $line[8];
                } else if ($value == 'female') {
                    $score += $line[9];
                }
            } else if($key == 'caffeine') {
                if (($value == '0') && ($line[10]=="1")) {
                    $score = -100;
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
            } else if($key == 'pain') {
                if($value == 'headache') {
                    $score += $line[16];
                } else if ($value == 'belly') {
                    $score += $line[17];
                } else if ($value == 'fever') {
                    $score += $line[18];
                } else if ($value == 'throat') {
                    $score += $line[19];
                } else if ($value == 'muscles') {
                    $score += $line[20];
                }
            } else if($key == 'yourday') {
                if($value == 'tranquil') {
                    $score += $line[21];
                } else if ($value == 'physical') {
                    $score += $line[22];
                } else if ($value == 'emotional') {
                    $score += $line[23];
                }
            } else if($key == 'weather') {
                if($value == 'rain') {
                    $score += $line[24];
                } else if ($value == 'cold') {
                    $score += $line[25];
                } else if ($value == 'cool') {
                    $score += $line[26];
                } else if ($value == 'warm') {
                    $score += $line[27];
                } else if ($value == 'hot') {
                    $score += $line[28];
                }
        }
    }

    if ($score<0) $score = 0;
    $teaScores[$line[0]] = $score; 
    $imageURLs[$line[0]] = $image_url;
    $indexes[$line[0]] = $index;
}
    // sort tea scores
    arsort($teaScores);


}

?>



<!DOCTYPE html>
<html>
<head>
    <title>Tea Time</title>
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
        <h1>Tea Time - Find the Perfect Tea For You Now</h1>
        <?php if(isset($_POST['submit'])) { ?>

        <div id="results">
        <p>Here are the best options for you:</p>
            <div class="results-list">
            <?php
                // present tea scores to the user
                $count = 1;
                foreach($teaScores as $title => $score) {
                    $url = $imageURLs[$title];
                    $index = $indexes[$title];
                    
                    if (($count<=3)&&($title!=='title')) {
                        echo "<div class='list-line'><img class='tea-thumb' src='" . $url . "' /> <a href='/tea-page.php?id=" . $index . "'><span class='name-mark'>" . $title . ': ' . $score . '</span></a></div>';
                        $count = $count + 1;
                    }
                }
                ?>
            </div>
        </div>
        <?php
        }
        ?>
        <div id="description">
            <p class="top-description">Welcome to Tea Time! Tea Time is a service to help you find the perfect tea for your needs. We'll ask you a few questions about your health, day, and mood to decide what tea you should drink.</p>
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
                <div class="label">Did you just wakeup?</div> 
                <select name="wakeup">
                    <option value="1" <?php if (isset($_POST['wakeup']) && $_POST['wakeup'] == '1') echo 'selected="selected"'; ?>>yes</option>
                    <option value="0" <?php if (isset($_POST['wakeup']) && $_POST['wakeup'] == '0') echo 'selected="selected"'; ?>>no</option>
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
                <div class="label">Is caffeine ok?</div> 
                <select name="caffeine">
                    <option value="1" <?php if (isset($_POST['caffeine']) && $_POST['caffeine'] == '1') echo 'selected="selected"'; ?>>yes</option>
                    <option value="0" <?php if (isset($_POST['caffeine']) && $_POST['caffeine'] == '0') echo 'selected="selected"'; ?>>no</option>
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
                <div class="label">Are you in pain?</div>
                <select name="pain">
                    <option value="none">No pain</option>
                    <option value="headache" <?php if (isset($_POST['pain']) && $_POST['pain'] == 'headache') echo 'selected="selected"'; ?>>headache</option>
                    <option value="belly" <?php if (isset($_POST['pain']) && $_POST['pain'] == 'belly') echo 'selected="selected"'; ?>>belly</option>
                    <option value="fever" <?php if (isset($_POST['pain']) && $_POST['pain'] == 'fever') echo 'selected="selected"'; ?>>fever</option>
                    <option value="throat" <?php if (isset($_POST['pain']) && $_POST['pain'] == 'throat') echo 'selected="selected"'; ?>>throat</option>
                    <option value="muscles" <?php if (isset($_POST['pain']) && $_POST['pain'] == 'muscles') echo 'selected="selected"'; ?>>muscles</option>
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
                <div class="label">What's the weather?</div>
                <select name="weather">
                    <option value="rain" <?php if (isset($_POST['weather']) && $_POST['weather'] == 'rain') echo 'selected="selected"'; ?>>rain</option>
                    <option value="cold" <?php if (isset($_POST['weather']) && $_POST['weather'] == 'cold') echo 'selected="selected"'; ?>>cold</option>
                    <option value="cool" <?php if (isset($_POST['weather']) && $_POST['weather'] == 'cool') echo 'selected="selected"'; ?>>cool</option>
                    <option value="warm" <?php if (isset($_POST['weather']) && $_POST['weather'] == 'warm') echo 'selected="selected"'; ?>>warm</option>
                    <option value="hot" <?php if (isset($_POST['weather']) && $_POST['weather'] == 'hot') echo 'selected="selected"'; ?>>hot</option>
                </select>
            </div>
            <br>
            <input class="send" type="submit" name="submit" value="Find my tea!">
        </form>
        <div id="tea-info">
            <p class="bottom-description" >Tea has been used for centuries to help people relax and rejuvenate. Not only is it a delicious beverage, but it has many health benefits as well. Whether you enjoy a cup of chamomile or a cup of black tea, you're sure to find something that fits your needs. So why wait? Let's get started!</p>
            <h1 id="Back" ><a href="/">Back</a></h1>
        </div>
    </div>
</body>
</html>