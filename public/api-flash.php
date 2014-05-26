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

$inputSupported = array("getVidById");

$docs = <<< DOC
- getVidById - Gets infomation on a video by it's video id. Param: Video Id [string]<br />
DOC;

if (!isset($_GET['method']) || !in_array($_GET['method'], $inputSupported)) {
	echo "Method not found.<br /><br />";
	echo "Available methods are:<br /><br />";
	echo $docs;
	echo "<br />Ex: ?method=getVidById&param=mfx-pornonoise";
} else {
	$toencode = call_user_func($_GET['method'],$_GET['param']);
	$info = array();
	if ($toencode->links != null) {
		$newlinks = array();
		foreach ($toencode->links as $vals) {
			$newlinks[] = '<a target="_blank" href="'.$vals->url.'"><u>'.$vals->name.'</u></a>';
		}
		$toencode->links = implode(' - ',$newlinks);
	}
	foreach ($toencode as $var => $data) {
		if (is_array($data) || is_object($data)) continue;
		$info[$var] = urlencode($var) . "=" . urlencode($data);
	}
	echo implode("&",$info);
}

?>