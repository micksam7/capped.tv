<?php header("Content-Type: application/rss+xml"); echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>

<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:capped="http://capped.tv/api-docs.php"
	xmlns:sceneid="http://scene.org/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	>

<channel>
	<title>Capped.TV Latest Vids</title>
	<atom:link href="http://capped.tv/rss.php" rel="self" type="application/rss+xml" />
	<link>http://capped.tv</link>
	<description>Latest Posted Videos on Capped.TV</description>
	<pubDate><?=date('r') ?></pubDate>
	<generator>http://capped.tv/ API-XML/RSS</generator>
	<language>en</language>
	<ttl>15</ttl>
<?php 
require "api-functions.php";

if (isset($_GET['num'])) {
	$numOf = (int) $_GET['num'];
	if ($numOf == -1) {
		$vids = getAllVids();
	} else {
		$vids = getLatestVids($numOf);
	}
} else {
	$vids = getLatestVids(15);
}

foreach ($vids as $video) {
	$name = htmlentities($video->name);
	$author = htmlentities($video->author);
	$width = $video->width;
	$height = $video->height+20;
	$date = $tm=date("D, d M Y H:i:s",$video->postTime)." GMT";
	$uploader = htmlentities($video->uploader);
	$player = '<center><object width="'.$width.'" height="'.$height.'"><param name="movie" value="http://capped.micksam7.com/playeralt.swf?vid='.$video->id.'" /><param name="wmode" value="transparent" /><param name="allowFullScreen" value="true" /><param name="bgcolor" value="#000000" /><embed src="http://capped.micksam7.com/playeralt.swf?vid='.$video->id.'" type="application/x-shockwave-flash" wmode="transparent" bgcolor="#000000" allowFullScreen="true" width="'.$width.'" height="'.$height.'"></embed></object><br /><br />';
	foreach ($video->links as $link)
		$player .= "<a href='{$link->url}'>{$link->name}</a> - ";
	$player .= "<a href=\"mailto:capped@micksam7.com?subject=Report/Remove/Edit Request for {$video->id}\">Report</a></center>";
	$comment = htmlentities($video->comment);
	$size = $video->size;
	if ($video->id == "limpninja-unicodeworksoncapped") continue;
	?>

	<item>
		<title><?=$name ?> by <?=$author ?></title>
		<link>http://capped.tv/<?=$video->id ?></link>
		<pubDate><?=$date ?></pubDate>
		<dc:creator><?=$uploader ?></dc:creator>
		<guid isPermaLink="true">http://capped.tv/player.php?vid=<?=$video->id ?></guid>
		<description><![CDATA[<?=$player ?>]]></description>
		<capped:id><?=$video->id ?></capped:id>
		<capped:width><?=$video->width ?></capped:width>
		<capped:height><?=$video->height ?></capped:height>
		<capped:bitrate><?=$video->bitrate ?> kbps</capped:bitrate>
		<capped:views><?=$video->views ?></capped:views>
		<sceneid:uploaderId><?=$video->uploaderId ?></sceneid:uploaderId>
		<capped:comment><?=$comment ?></capped:comment>
		<capped:videoLink><?=$video->videoLink ?></capped:videoLink>
		<capped:flv6Link><?=$video->flv6Link ?></capped:flv6Link>
		<capped:imageLink><?=$video->imageLink ?></capped:imageLink>
		<capped:audioLink><?=$video->audioLink ?></capped:audioLink>
		<capped:nfoLink><?=$video->nfoLink ?></capped:nfoLink>
		<enclosure url="<?=$video->videoLink ?>" length="<?=$size ?>" type="video/mp4" />
	</item>
	<?php } ?>
	</channel>
</rss>
