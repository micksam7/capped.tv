<?php
/**
 * Capped.TV Player 3.0
 * 
 * @author micksam7
 * @link http://micksam7.com/
 * @copyright 2007-2010, micksam7
 */

require_once "api-functions.php";

if (isset($_SERVER['HTTP_USER_AGENT']) && (stripos($_SERVER['HTTP_USER_AGENT'],"iPhone") !== false || stripos($_SERVER['HTTP_USER_AGENT'],"Android") !== false)) {
	define("NOHTML5VIDEO",false);
	if (isset($_GET['vid']) && strpos($_GET['vid'],"|") === false) {
		$_GET['vid'] .= "|mq";
		$vid = getVidById($_GET['vid']);
	}
} else {
	define("NOHTML5VIDEO",isset($_COOKIE['noHtml5Video']) && $_COOKIE['noHtml5Video'] == 1);
}
//define("NOHTML5VIDEO",true);

if ($_GET['vid'] == "1206402714-1-0") $_GET['vid'] = "asd-metamorphosis";
	

//Get video and misc info
if (!isset($vid)) {
	$vid = getVidById($_GET['vid']);
}

if (count($vid) == 0) {
	header("HTTP/1.1 404 Not Found");
	echo "Yep. Didn't find that.";
	exit();
}

//Set title info
$title = $vid->name . ($vid->author == null ? "" : " by ".$vid->author);

//Compute out them links
$links = null;
foreach ($vid->links as $link)
$links .= "- <a href='{$link->url}'>{$link->name}</a><br />";

$links = $links . "<br />";

if (count($vid->vidHQ) != 0) $links .= "- <a href=\"{$vid->vidHQ['link']}\">HQ MP4 Download</a><br />";
if (count($vid->vidMQ) != 0) $links .= "- <a href=\"{$vid->vidMQ['link']}\">Mobile MP4 Download</a><br />";
$links .= "- <a href=\"mailto:capped@micksam7.com?subject=".urlencode("Report/Remove/Edit Request for {$vid->id}")."\">Report</a>";

$versions = array();

if ($vid->vidHQ) {
	$string = $vid->quality == "hq" ? "<b>High Quality</b>: " : "<a href='/{$vid->realId}|hq'>High Quality</a>: ";
	$string .= "{$vid->vidHQ['width']}x{$vid->vidHQ['height']} @ ".number_format($vid->vidHQ['bitrate'])." kbit";
	$string .= " <span class='info'>[Max CPU-Burning Quality h.264]</span>";
	$versions[] = $string;
}
if ($vid->vidMQ) {
	$string = $vid->quality == "mq" ? "<b>Medium Quality</b>: " : "<a href='/{$vid->realId}|mq'>Medium Quality</a>: ";
	$string .= "{$vid->vidMQ['width']}x{$vid->vidMQ['height']} @ ".number_format($vid->vidMQ['bitrate'])." kbit";
	$string .= " <span class='info'>[Mainline Medium Quality h.264]</span>";
	$versions[] = $string;
}
if ($vid->vidLQ) {
	$string = $vid->quality == "lq" ? "<b>Low Quality</b>: " : "<a href='/{$vid->realId}|lq'>Low Quality</a>: ";
	$string .= "{$vid->vidLQ['width']}x{$vid->vidLQ['height']} @ ".number_format($vid->vidLQ['bitrate'])." kbit";
	$string .= " <span class='info'>[Low Res Decent Quality FLV]</span>";
	$versions[] = $string;
}

if (count($versions) > 1) {
	$versions = implode("<br />",$versions);
	$versions = "<div class=\"graybox resSelector\">{$versions}</div>";
} else {
	$versions = "";
}

/*if ($vid->mirrorInfo != false) {
	if ($vid->mirrorInfo['name'] == "CDN") $vid->mirrorInfo['name'] = "our fancy CDN";
	$mirror = "You're using {$vid->mirrorInfo['name']} for Capped.TV.<br />Located in beautiful {$vid->mirrorInfo['location']}.";
} else */
$mirror = "";

if ($vid->pouetId != 0) {
	$page = httpCacheSocket("http://www.pouet.net/export/prod.xnfo.php?which=".$vid->pouetId);

	try {
		$page = new SimpleXMLElement($page,LIBXML_NOERROR);
	} catch (Exception $e) {
		$pouet = null;
	}

	$pouet = array();

	if (isset($page->demo->category)) {
		$temp = array();
		foreach ($page->demo->category as $type) {
			$temp[] = (string) strip_tags($type);
		}
		$pouet['type'] = implode(", ",$temp);
	}


	if (isset($page->demo->support->configuration->platform)) {
		$temp = array();
		foreach ($page->demo->support->configuration->platform as $type) {
			$temp[] = (string) strip_tags($type);
		}
		$pouet['platform'] = implode(", ",$temp);
	}

	if (isset($page->demo->releaseDate)) {
		$pouet['date'] = strip_tags((string) $page->demo->releaseDate);
	}

	if (isset($page->demo->release->party) && isset($page->demo->release->date)) {
		$pouet['party'] = strip_tags((string) $page->demo->release->party) . " " . strip_tags((string) $page->demo->release->date);
	}

	if (isset($page->demo->release->compo)) {
		$pouet['compo'] = strip_tags((string) ucwords($page->demo->release->compo));
	}

	if (isset($page->demo->release->rank)) {
		$pouet['ranked'] = strip_tags((string) $page->demo->release->rank);
		if ($pouet['ranked'] == 0) $pouet['ranked'] = "";
	}
}

if (isset($pouet) && count($pouet) > 0) {
	$newPouet = array();
	foreach ($pouet as $id => $data) {
		if (trim($data) == "") $data = "<span style=\"color: #AAAAAA;\">N/A</span>";
		$newPouet[] = "<b>".ucfirst($id).":</b> {$data}";
	}
	$newPouet = implode("<br />",$newPouet);
	$newPouet = "<b class=\"title\">Prod Info</b><br />{$newPouet}<br /><br />More Info on <a href=\"http://pouet.net/prod.php?which={$vid->pouetId}\">Pouet</a>";
}

$statsId = preg_replace("/[^A-Za-z0-9 ]/", '', $vid->realId);

$headers = "
		<script type='text/javascript'>
			var ac_width  = {$vid->width};
			var ac_height = {$vid->height};
			var ac_vid    = '{$vid->realId}';
			var ac_hq	= ".(count($vid->vidHQ)==0?"false":"true").";
			var ac_mq	= ".(count($vid->vidMQ)==0?"false":"true").";
			var ac_lq	= ".(count($vid->vidLQ)==0?"false":"true").";".
			"
		</script>
";

?>
<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" dir="ltr">
	<head>
		<!-- Layout and design is Copyright 2010 micksam7 - me@micksam7.com -->
		<title><?=$title ?> - Capped.TV</title>
		<meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
		<script src="http://img.capped.tv/img/player.js" type="text/javascript" charset="utf-8"></script>
		<script type="text/javascript" src="http://cdn.sublimevideo.net/js/zf8jbler.js"></script>
		<script type="text/javascript">
			<?php
				if (isset($_GET['autoplay']) && $_GET['autoplay'] != "false") {
			?>sublimevideo.ready( function() {
				sublimevideo.play("videoBox");
				});
			<?php } ?>
		</script>
		<?=$headers ?>
		<link rel="stylesheet" href="http://capped.tv/img/player.css" type="text/css" media="all" />
		<!--[if lt IE 7]>
			<script type="text/javascript" src="http://img.capped.tv/img/pngfix.js"></script>
			<script type="text/javascript">
				DD_belatedPNG.fix('div, a, b, img');
			</script>
		<![endif]-->
	</head>
	<body>
		<div class="video" style="width: <?=$vid->width ?>px; height:<?=$vid->height ?>px;">
		<?php
			if ($vid->quality != "lq") {
		?>

			<video id="videoBox" class="sublime" width="<?=$vid->width ?>" height="<?=$vid->height ?>" poster="<?=$vid->imageLink ?>" preload="none" <?=!isset($_GET['autoplay']) || $_GET['autoplay'] == "false" ? "" : "autoplay" ?> data-uid="<?=$statsId ?>">
				<source src="<?=$vid->videoLink ?>" type='video/mp4; codecs="avc1.<?=($vid->quality == "hq" ? "64001E" : "42E01E") ?>, mp4a.40.2"'>
			</video>
		<?php } else { ?>
				<object class="player" type="application/x-shockwave-flash" width="<?=$vid->width ?>" height="<?=$vid->height+20 ?>" data="http://capped.tv/play.swf?vid=<?=$vid->id ?>&amp;autoplay=<?=!isset($_GET['autoplay']) || $_GET['autoplay'] == "false" ? "off" : "on"; ?>&amp;nolink=true" id="playerobj">
					<param name="allowFullScreen" value="true" />
					<param name="bgcolor" value="#000000" />
					<param name="movie" value="/play.swf?vid=<?=$vid->id ?>&amp;autoplay=<?=!isset($_GET['autoplay']) || $_GET['autoplay'] == "off" ? "off" : "on"; ?>&amp;nolink=true" />
					<param name="quality" value="high" />
					<param name="wmode" value="direct" />
				</object>
		<?php
			}
		?>

		</div>
		
		<div class="vidinfo">
			<div class="<?=isset($newPouet)?"graybox":"" ?> leftinfo">
				<?=isset($newPouet)?$newPouet:"" ?>

			</div>
			<div class="graybox rightinfo">
			<b class="title">Video Info</b><br />
			<b>Uploader:</b> <a class="sil" href="http://capped.tv/user,<?=$vid->uploaderId ?>"><?=$vid->uploader ?></a><br />
			<?php if ($vid->comment != null) { echo "<i>&quot;".htmlentities($vid->comment)."&quot;</i><br />"; } ?>
			<br />
			<?=$links ?>
			
			</div>
			<div class="middleinfo">
		
			<?=$versions ?>
			<img src="http://img.capped.tv/img/seperator-300px.png" class="seperator" />
			<div class="blankinfo">
<form name="embed" onsubmit="return updateEmbed();">
Embed: <input type="text" name="code" value='<object width="<?=$vid->width ?>" height="<?=$vid->height ?>"><param name="movie" value="http://capped.tv/play.swf?vid=<?=$vid->id ?>" /><param name="wmode" value="direct" /><param name="allowFullScreen" value="true" /><param name="bgcolor" value="#000000" /><embed src="http://capped.tv/play.swf?vid=<?=$vid->id ?>" wmode="direct" bgcolor="#000000" allowFullScreen="true" width="<?=$vid->width ?>" height="<?=$vid->height ?>" type="application/x-shockwave-flash"></embed></object>' /><br /><br />
- Custom Size Embed -<br />Width: <input type="text" style="width:50px;" name="widthh" /> <input type="submit" value="Update" /> </form>
			</div>
			<img src="http://img.capped.tv/img/seperator-300px.png" class="seperator" />
				<div class="blankinfo">
					Capped.TV 2006-<?=date("Y") ?><br /><br />
					Production belongs to their respective owners<br />
					Try not to use it for commerical purposes, kay?
				</div>
			</div>
		</div>
			
		<br /><br /><br /><br />
		
		<a href="/"><img src="http://img.capped.tv/img/arrowback.png" alt="Go Back" class="back" /></a>
	</body>
</html>