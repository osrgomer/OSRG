<head>
    
    <link rel="stylesheet" href="trailer-page.css">
    
    
</head>
<body>
<?php

require ("functions.php");
$id = $_GET['id'] + 1;

    get_movie($id);

?>

<h2><?php echo $title; ?></h2>


<h2>Description: <?php echo $description; ?></h2>
<br/>
<h2>Where to watch:</h2>
 <div class="itemdiv">
	  <a href="<?php echo $watch_link; ?>">
	    <h2 class="itemheading"><?php echo $watch; ?></h2>
 	  </a>
 	</div>





<div class="itemdiv">
	  <a href="<?php echo $info_link; ?>">
	    <h2 class="itemheading"><?php echo $wikilink; ?></h2>
 	  </a>
 	</div>








<h1><a href="/">Back to website</a></h1>

</body>