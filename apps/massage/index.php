<html>




<head>
    </script>
        <script type="text/javascript">
function googleTranslateElementInit() {
  new google.translate.TranslateElement({pageLanguage: 'en'}, 'google_translate_element');
}
</script>

<script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="manifest" href="/site.webmanifest">

    <link rel="stylesheet" href="nommassage-style.css">
    <title>nomi-massage</title>
</head>
    <body>
        <div id="google_translate_element"></div>
    <h1>welcome to nomi's online face massage videos</h1>
    <br/>
    <br/>
    
    
    
    <?php


$path = getcwd() . "/photos_videos/*.mp4";

$latest_ctime = 0;
$latest_filename = '';

$files = glob($path);
foreach($files as $file)
{
        if (is_file($file) && filectime($file) > $latest_ctime)
        {
                $latest_ctime = filectime($file);
                $latest_filename = $file;
                $short = basename($latest_filename);
                $title = substr($short, 0, strrpos($short, "."));
        } 
}

?>

<html>
    <head>
    <link rel="stylesheet" href="nommassage-style.css">
    </head>
     <body>
         
<h1 id = "h1_top" >first video 👇</h1>

<br/>
<h1><?php echo $title; ?></h1>
<br/>
<div class="video1">
	    <video controls width = "512" src="photos_videos/<?php echo $short; ?>" />
	  </div>
	  
	  <div class="video2">
	      <h1>this massage is with eyebrows</h1>
	    <video controls width = "512" src="photos_videos/this massage is with eyebrows.mp4" />
	  </div>
	 
	  
<br/>	  
<br/>
<br/>
 
  <div class = "div_back">
<h1 class = "back"><a href="/">Back to the main website</a></h1> 
</div>
<br/>

 </body>   
</html>