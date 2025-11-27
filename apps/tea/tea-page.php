<head>
    
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="tea-page.css">
    
    
</head>
<body>
<?php
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$csvFile = fopen('tea.csv', 'r');
if (!$csvFile) {
    die('Error: Cannot open tea.csv file.');
}

$counter = 0;
$title = $description = $buy = $wikilink = $image_url = '';

while (($line = fgetcsv($csvFile)) !== false) {
    if ($counter == $id) {
        $title = isset($line[0]) ? $line[0] : '';
        $description = isset($line[1]) ? $line[1] : '';
        $image_url = isset($line[29]) ? $line[29] : '';
        $wikilink = isset($line[30]) ? $line[30] : '';
        $buy = isset($line[31]) ? $line[31] : '';
        break;
    }
    $counter++;
}
fclose($csvFile);
?>

<h2><?php echo $title; ?></h2>


<h2>Description: <?php echo $description; ?></h2>
<br/>
<?php if($image_url): ?>
<img src="<?php echo htmlspecialchars($image_url); ?>" alt="<?php echo htmlspecialchars($title); ?>" style="max-width: 300px; height: auto;">
<br/><br/>
<?php endif; ?>

<?php if($buy): ?>
<h2>to buy:</h2>
 <div class="itemdiv">
	  <a href="<?php echo htmlspecialchars($buy); ?>">
	    <h2 class="itemheading">Buy <?php echo htmlspecialchars($title); ?></h2>
 	  </a>
 	</div>
<?php endif; ?>

<?php if($wikilink): ?>
<div class="itemdiv">
	  <a href="<?php echo htmlspecialchars($wikilink); ?>">
	    <h2 class="itemheading">Learn more about <?php echo htmlspecialchars($title); ?></h2>
 	  </a>
 	</div>
<?php endif; ?>















<h1><a href="/osrg/apps/tea/tea">Back to Tea Time</a></h1>

</body>