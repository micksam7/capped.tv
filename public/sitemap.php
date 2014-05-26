<?php header("Content-Type: text/xml"); echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">

<?php
	require "api-functions.php";

	$vids = getLatestVids(100000);
foreach ($vids as $id => $vid) {

$length = round($vid->length);
$postTime = date("c",$vid->postTime);
$name = htmlentities($vid->name);
$author = htmlentities($vid->author);
$uploader = htmlentities($vid->uploader);

echo <<< DOCHEREPLZ
   <url> 
     <loc>http://capped.tv/{$vid->realId}</loc>
     <video:video>
       <video:thumbnail_loc>{$vid->imageLink}</video:thumbnail_loc> 
       <video:title>{$name} by {$author}</video:title>
       <video:description></video:description>
       <video:content_loc>{$vid->videoLink}</video:content_loc>
       <video:player_loc allow_embed="yes">http://capped.tv/play.swf?vid={$vid->realId}</video:player_loc>
       <video:duration>{$length}</video:duration>
       <video:view_count>{$vid->views}</video:view_count>    
       <video:publication_date>{$postTime}</video:publication_date>
       <video:tag>demoscene</video:tag> 
       <video:tag>demo</video:tag>
       <video:category>Demoscene</video:category>
       <video:uploader>{$uploader}</video:uploader>
     </video:video> 
   </url>

DOCHEREPLZ;
}

?> 
</urlset>