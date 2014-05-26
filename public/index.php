<?php
/**
 * Capped.TV Index
 * 
 * @author micksam7
 * @link http://micksam7.com/
 * @copyright 2007-2014, micksam7
 */

require_once "api-functions.php";

//Some hard vars
define("VIDLIMIT",10);
define("NOVIDEOTHUMBS",isset($_COOKIE['noVideoThumbs']) && $_COOKIE['noVideoThumbs'] == 1);

//Sorting menu
$sortByLinks = array(
	"new"   => array("title"=>"Newest","url"=>"/newest"),
	"view"  => array("title"=>"Most Views","url"=>"/mostviews"),
	"month" => array("title"=>"Top of the Month","url"=>"/monthtop"),
	"alpha" => array("title"=>"Alphabetical","url"=>"/alpha"),
);

//What to do.. [$params is off 404.php loader]
if (isset($params) && isset($params[0])) {
	if (($params[0] == "user" || $params[0] == "search") && isset($params[2]) && is_numeric($params[2])) {
		$offset = (int) $params[2];
	} else if ($params[0] != "user" && $params[0] != "search" && isset($params[1]) && is_numeric($params[1])) {
		$offset = (int) $params[1];
	} else {
		$offset = false;
	}
	
	if ($offset < 0) $offset = false;
	if ($offset % VIDLIMIT != 0) $offset = floor($offset / VIDLIMIT) * VIDLIMIT;
	if ($offset == 0) $offset = false;
	
	$currentModule = $params[0];
	
	switch (strtolower($params[0])) {
		case 'newest':
			$videos = getLatestVids(VIDLIMIT,$offset);
			$title = "Newest Videos";
			$sortByLinks['new']['title'] = "Oldest";
			$sortByLinks['new']['url'] = "/oldest";
			break;
		case 'oldest':
			$videos = getLatestVids(VIDLIMIT,$offset,true);
			$title = "Oldest Videos";
			break;
		case 'mostviews':
			$videos = getTopVids(VIDLIMIT,$offset);
			$title = "Most Viewed Videos";
			$sortByLinks['view']['title'] = "Least Views";
			$sortByLinks['view']['url'] = "/leastviews";
			break;
		case 'leastviews':
			$videos = getTopVids(VIDLIMIT,$offset,true);
			$title = "Least Viewed Videos";
			break;
		case 'monthtop':
			$videos = getTopMonthVids(VIDLIMIT,$offset);
			$title = "Most Viewed Videos this Month";
			$sortByLinks['month']['title'] = "Bottom of the Month";
			$sortByLinks['month']['url'] = "/monthbottom";
			break;
		case 'monthbottom':
			$videos = getTopMonthVids(VIDLIMIT,$offset,true);
			$title = "Least Viewed Videos this Month";
			break;
		case 'alpha':
			$videos = getAllVids(VIDLIMIT,$offset);
			$title = "Alphabetical Video List by Group then Title";
			$sortByLinks['alpha']['title'] = "lacitebahplA";
			$sortByLinks['alpha']['url'] = "/ahpla";
			break;
		case 'ahpla':
			$videos = getAllVids(VIDLIMIT,$offset,true);
			$title = "Reversed Alphabetical Video List by Group then Title";
			break;
		case 'user':
			if (!isset($params[1])) break;
			$videos = findVidsByUserId($params[1],VIDLIMIT,$offset);
			$uid = (int) $params[1];
			$currentModule = "user,{$uid}";
			$title = "Videos Kindly Uploaded by Biological Lifeform #{$uid}";
			break;
		case 'search':
			$search = isset($_GET['s']) ? $_GET['s'] : (isset($params[1]) ? $params[1] : false);
			if (!$search) break;
			$videos = search($search,VIDLIMIT,$offset);
			$search = str_replace(",","",$search);
			$currentModule = "search,".urlencode($search);
			$search = htmlentities($search);
			$title = "Searching for {$search}";
			if (count($videos) == 0) {
				$search = "No Results Found"; //Meh.
			} 
			break;
		default:
			$vid = getVidById($params[0]);
			if ($vid != false) { //!!
				$_GET['vid'] = $params[0];
				require "player.php";
				exit();
			}
			break;
	}
}

if (!isset($videos) || $videos == false) { //Didn't select any :(
	$currentModule = "newest";
	$title = "Turning Fruit into Juice";
	$videos = getLatestVids(VIDLIMIT);
	$offset = false;
}

//Pre-process selector
foreach ($sortByLinks as $id => $value) {
	$sortByLinks[$id] = "<a href=\".{$value['url']}\">{$value['title']}</a>";
}

?>
<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" dir="ltr">
	<head>
		<!-- Layout and design is Copyright 2010 micksam7 - me@micksam7.com -->
		<title>Capped.TV - <?=$title ?></title>
		<meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
		<script type="text/javascript" src="http://img.capped.tv/img/frames.js"></script>
		<link rel="stylesheet" href="http://img.capped.tv/img/css.css" type="text/css" media="all" />
		<link rel="stylesheet" href="http://img.capped.tv/img/smaller.css" type="text/css" media="handheld, screen and (max-width: 1044px)" />
		<link rel="alternate" href="http://capped.tv/rss.php" type="application/rss+xml" title="Capped.TV RSS Feed" />
		<!-- Add some text effects for IE -->
		<!--[if lt IE 9]>
			<link rel="stylesheet" href="http://img.capped.tv/img/ie.css" type="text/css" media="all" />
		<![endif]-->
		<!-- Fix some IE6-7 oddities [Capped works fine in IE8, amazingly] -->
		<!--[if lt IE 8]>
			<link rel="stylesheet" href="http://img.capped.tv/img/ie7.css" type="text/css" media="all" />
		<![endif]-->
		<!-- And fix all IE6's issues [mostly png stuff] -->
		<!--[if lt IE 7]>
			<script type="text/javascript" src="http://img.capped.tv/img/pngfix.js"></script>
			<script type="text/javascript">
				DD_belatedPNG.fix('div, a, b, img');
			</script>
			<link rel="stylesheet" href="http://img.capped.tv/img/ie6.css" type="text/css" media="all" />
		<![endif]-->
	</head>
	<body>
		<!-- Clipper keeps the logo from flying off the screen -->
		<div class="clipper"><div class="logo"><img class="logo" width="2574" height="260" src="http://img.capped.tv/img/logo.png" alt="Capped.TV" /></div></div>
		<div id="debug"></div>
		<!-- Container is a silly hack to keep the info boxes close by but not too far. -->
		<div class="container">
		<div class="info">
			<h2>So Hey</h2>
			Welcome to Capped.TV.<br />
			Yet another attempt to bring <a class="link" href="http://en.wikipedia.org/wiki/Demoscene">Demos</a> to the masses and lazy sceners.<br />
			Here you'll find high-quality <a class="link" href="http://en.wikipedia.org/wiki/Demoscene">Demoscene</a> videos, most of them <a class="link" href="http://en.wikipedia.org/wiki/Screencaps">screen captures</a> of demos from their native hardware [or sometimes emulators], uploaded by our various users.<br />
			Go ahead. Watch. Enjoy.
			<h3>New Stuff</h3>
			- Nothing!
			<br /><br />
			&gt; <a class="selection" href="upload.php">Upload Here</a>
			<br /><br />
		</div>
		<div class="search">
			<form action="./search" method="get"><input type="text" class="searchbox" name="s" value="<?=isset($search)?$search:"" ?>" /><input class="searchsubmit" type="submit" value="Search" /></form>
		</div>
		<div class="selectionBackdrop"><div id="selection1" class="selection"><?=implode($sortByLinks,"<span class=\"selSep\"> | </span>") ?></div></div>
<?php 
		foreach ($videos as $id => $video) {
?>
		<div id="frame<?=$id ?>" class="vidBackdrop">
			<div class="vidBox" onclick="location.href='./<?=$video->realId ?>';">
				<a href="./<?=$video->realId ?>">
<?php if (isset($video->thumb['videos']) && !NOVIDEOTHUMBS) { ?>
				<video id="thumb<?=$id ?>" class="thumb" autoplay loop width="160" height="90" poster="<?=$video->thumb['image'] ?>">
					<?php if (isset($video->thumb['videos']['mp4'])) { ?><source src="<?=$video->thumb['videos']['mp4'] ?>" type='video/mp4; codecs="avc1.42E01E"' /><?php } ?>
					<?php if (isset($video->thumb['videos']['webm'])) { ?><source src="<?=$video->thumb['videos']['webm'] ?>" type='video/webm; codecs="vp8, vorbis"' /><?php } ?>
					<?php if (isset($video->thumb['videos']['ogv'])) { ?><source src="<?=$video->thumb['videos']['ogv'] ?>" type='video/ogg; codecs="theora"' /><?php } ?>
					<img class="thumb" src="<?=$video->thumb['image'] ?>" width="160" height="90" />
				</video>
<?php } else { ?>
					<img class="thumb" src="<?=$video->thumb['image'] ?>" width="160" height="90" />
<?php } ?>
				</a>
				<div class="vidSep"></div>
				<div class="vidTitle"><a href="./<?=$video->realId ?>"><?=$video->name ?></a></div>
				<div class="vidAuthor"><a href="./<?=$video->realId ?>"><?=$video->author ?></a></div>
				<div class="vidInfo">
					<?=lengthTime($video->length) ?> - <?=number_format($video->views) ?> views - <?=number_format($video->monthViews) ?> this month<br />
					<?php if ($video->comment != null) { ?><i><?=htmlentities($video->comment)?></i> - <?php } else { echo "Uploaded by "; } ?><a href="user,<?=$video->uploaderId ?>"><?=$video->uploader ?></a>
				</div>
			</div>
		</div>
<?php } ?>
		<div id="arrows" class="arrows">
<?php if ($offset != false) { ?>
		<a href="<?=$currentModule.",".($offset-VIDLIMIT) ?>"><img id="arrowleft" class="arrowleft" src="http://img.capped.tv/img/arrowleft.png" alt="Back one Page" /></a>
<?php 
		} else {
			echo "<img id=\"arrowleft\" class=\"arrowleft\" src=\"img/blank.gif\" alt=\"\" />";
		}
		if (hasMore()) {
?>
		<a href="<?=$currentModule.",".($offset+VIDLIMIT) ?>"><img id="arrowright" class="arrowright" src="http://img.capped.tv/img/arrowright.png" alt="Forward one Page" /></a>
<?php
		} else {
			echo "<img id=\"arrowright\" class=\"arrowright\" src=\"img/blank.gif\" alt=\"\" />";
		}
?>
		<div class="selectionBackdrop"><div id="selection" class="selection"><?=implode($sortByLinks,"<span class=\"selSep\"> | </span>") ?></div></div>
		</div>
		</div>
		
		<div class="etc">
			<img src="http://img.capped.tv/img/seperator-300px.png" class="seperator" />
			<br /><br />
			Capped.TV is by <a href="http://micksam7.com/">micksam7</a>.
			<br /><br />
			<a href="http://scene.org/"><img class="link" src="http://img.capped.tv/img/scene.gif" alt="Scene.org" /></a>
			<a href="http://pouet.net/"><img class="link" src="http://img.capped.tv/img/pouet.gif" alt="Pouet" /></a>
			<a href="http://www.bitfellas.org/"><img class="link" src="http://img.capped.tv/img/bf.png" alt="Bitfellas" /></a>
			<br /><br />
			<a href="toggleit.php?what=noVideoThumbs&amp;whyyyyy">Turn Animated Thumbnails <?=NOVIDEOTHUMBS?"On":"Off"?>?</a>
			<div class="hidemeifnotsmall"><br /><br />
			<a href="upload.php">Upload Something</a></div>
			<br /><br />
			<img src="http://img.capped.tv/img/seperator-300px.png" class="seperator" />
		</div>
		
		<script type="text/javascript">
			/* This is mostly for later.. Till then, restarts videos on ff */
<?php for ($t = 0; $t < count($videos); $t++) { ?>
			frameAdd(document.getElementById("frame<?=$t ?>"));
<?php } ?>

			setInterval("frameInterval();",100);
		</script>
	</body>
</html>