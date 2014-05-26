<?php
//Capped.TV's Dirt
//By the Magic Dolphin [micksam7.com]

require "api-functions.php";

/* getVidById
 findVidsByPouetId
 findVidsByUserId
 getAllVids
 getLatestVids
 getTopVids
 getBottomVids
 getTopMonthVids */

$inputSupported = array("getVidById","findVidsByPouetId","findVidsByUserId","getAllVids","getLatestVids","getTopVids",
						"getBottomVids","getTopMonthVids");

$docs = <<< DOC
- getVidById - Gets infomation on a video by it's video id. Param: Video Id [string]<br />
- findVidsByPouetId - Gets all videos matching a pouet prod id. Param: Pouet Prod Id [int]<br />
- findVidsByUserId - Gets all videos uploaded by a person by their sceneid. Param: Sceneid ID [int]<br />
- getAllVids - Gets all videos. Period. Param: None<br />
- getLatestVids - Gets x amount of new videos. Param: Amount of videos to get, x [int]<br />
- getTopVids - Gets x amount of most-viewed videos. Param: Amount of videos to get, x [int]<br />
- getBottomVids - Gets x amount of least-viewed videos. Param: Amount of videos to get, x [int]<br />
- getTopMonthVids - Gets x amount of most-viewed videos of the month. Param: Amount of videos to get, x [int]<br />
DOC;

if (!isset($_GET['method']) || !in_array($_GET['method'], $inputSupported)) {
	echo "Method not found.<br /><br />";
	echo "Available methods are:<br /><br />";
	echo $docs;
	echo "<br />Ex: ?method=getVidById&param=mfx-pornonoise";
} else {
	$toencode = call_user_func($_GET['method'],$_GET['param']);
	var_export($toencode);
}

function xmlSpew($in,$tabs=0) {
	$return = array();
	$tab = str_repeat("\t",$tabs);
	foreach ($in as $element => $value) {
		if (is_numeric($element)) $element = "item";
		$element = htmlentities($element);
		if (is_array($value) || is_object($value)) $return[] = "{$tab}<{$element}>\n".xmlSpew($value,$tabs+1)."{$tab}</{$element}>\n";
		else $return[] = "{$tab}<{$element}>".htmlentities($value)."</{$element}>\n"; 
	}
	return implode("",$return);
}

?>