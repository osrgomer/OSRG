<?php


function get_movie($id) {
    global $title, $description, $wikilink, $watch, $watch_link, $info_link, $cast, $genre;
    
    $csvFile = fopen('movies.csv', 'r');
    $line_id = 0;
    $firstline = true;
    
    while ((($line = fgetcsv($csvFile)) !== false) && ($line_id < $id)) {
        // Skip header row
        if ($firstline) {
            $firstline = false;
            continue;
        }
        
        $title = isset($line[0]) ? $line[0] : '';
        $description = isset($line[1]) ? $line[1] : '';
        $trailer_url = isset($line[2]) ? $line[2] : '';
        $image_url = isset($line[17]) ? $line[17] : '';
        $info_link = isset($line[18]) ? $line[18] : '';
        $genre = isset($line[19]) ? $line[19] : '';
        $cast = isset($line[20]) ? $line[20] : '';
        // $watch_link removed
        $wikilink = "More Info";
        $watch = ""; // Removed "Watch Now" text
        $line_id++;
    }
    
    fclose($csvFile);
}