<head>
    
    <link rel="stylesheet" href="tea-page.css">
    
    
</head>
<body>
<?php

require ("functions.php");
$id = $_GET['id'] + 1;

    get_tea($id);

?>

<h2><?php echo $title; ?></h2>


<h2>Description: <?php echo $description; ?></h2>
<br/>
<h2>to buy:</h2>
 <div class="itemdiv">
	  <a href="https://www.naturitas.pt/p/alimentacao/bebidas/chas-e-infusoes/infusoes-relaxantes/cha-de-alcacuz-17-saquetas-de-infusao-yogi-tea">
	    <h2 class="itemheading"><?php echo $buy; ?></h2>
 	  </a>
 	</div>





<div class="itemdiv">
	  <a href="https://en.wikipedia.org/wiki/Liquorice">
	    <h2 class="itemheading"><?php echo $wikilink; ?></h2>
 	  </a>
 	</div>















<h1><a href="/">Back to website</a></h1>

</body>