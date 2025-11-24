<?php


function get_tea($id) {
    global $title, $description, $wikilink, $buy;
    
    $csvFile = fopen('https://docs.google.com/spreadsheets/d/e/2PACX-1vQ7FGmJj4Tfi5l8vlzNgb4Rn52WpL-ix8f9t9poB2zXJ4r5LF1u9DyRH8DWE6myLiWcas1rlJjuJDux/pub?gid=0&single=true&output=csv', 'r');
    $line_id = 0;    
    while ((($line = fgetcsv($csvFile)) !== false)&&($line_id<$id)) {
        $image_url = $line[29];
        $wikilink = $line[30];
        $buy = $line[31];
        $title = $line[0];
        $description = $line[1];
        $line_id++;

    }

    
    
    
    
    

}