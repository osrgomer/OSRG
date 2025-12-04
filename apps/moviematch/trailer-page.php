<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Movie Trailer</title>
    <link rel="stylesheet" href="trailer-page.css">
</head>
<body>
<?php

require ("functions.php");
$id = $_GET['id'] + 1;

get_movie($id);

// Get trailer URL from the CSV
$csvFile = fopen('movies.csv', 'r');
$line_id = 0;
$firstline = true;
$trailer_id = '';

while (($line = fgetcsv($csvFile)) !== false) {
    if ($firstline) {
        $firstline = false;
        continue;
    }
    
    if ($line_id == $id - 1) {
        $trailer_id = isset($line[2]) ? $line[2] : '';
        break;
    }
    $line_id++;
}
fclose($csvFile);

?>

<div class="container">
    <h1><?php echo htmlspecialchars($title); ?></h1>
    
    <?php if (!empty($trailer_id)): ?>
    <div class="video-container">
        <iframe 
            width="100%" 
            height="450" 
            src="https://www.youtube.com/embed/<?php echo htmlspecialchars($trailer_id); ?>?autoplay=1" 
            title="YouTube video player" 
            frameborder="0" 
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
            allowfullscreen>
        </iframe>
    </div>
    <?php else: ?>
    <p style="text-align: center; color: #ff6b6b;">No trailer available for this movie.</p>
    <?php endif; ?>
    
    <div class="info-section">
        <h2>Description:</h2>
        <p><?php echo htmlspecialchars($description); ?></p>
        
        <?php if (!empty($genre)): ?>
        <div class="genre-badge">
            <strong>Genre:</strong> <?php echo htmlspecialchars($genre); ?>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($cast)): ?>
        <div class="cast-section">
            <h3>Cast:</h3>
            <p class="cast-list"><?php echo htmlspecialchars($cast); ?></p>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="links-section">
        <?php if (!empty($info_link)): ?>
        <div class="itemdiv">
            <a href="<?php echo htmlspecialchars($info_link); ?>" target="_blank">
                <h2 class="itemheading">More Info (IMDB)</h2>
            </a>
        </div>
        <?php endif; ?>
    </div>

    <div class="back-button">
        <a href="index.php">← Back to MovieMatch</a>
    </div>
</div>

</body>
</html>