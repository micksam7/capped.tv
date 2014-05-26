<?php

if ($_POST['id'] != null) $videoId = $_POST['id']; else $videoId = $_GET['id'];

if (strpos($videoId,"|") !== false) {
	list($videoId,$quality) = explode("|",$videoId);
	if ($quality != "hq" && $quality != "mq" && $quality != "lq") {
		$quality = "hq";
	}
} else {
	$quality = "hq";
}

if ($videoId != "blank" && $videoId != null && strlen($videoId) < 255) {
	require "funky.php";
	$db = getdatabase();
	$refer = $_SERVER['HTTP_REFERER'];
	if (strlen($refer) > 650) $refer = "too long";
	mysql_unbuffered_query("INSERT INTO ac_views (vidid, ip, finish, time, refer) VALUES (\"".addslashes($videoId)."\", \"".addslashes($_SERVER['REMOTE_ADDR'])."\", \"".($_GET['state']=="stop"?1:0)."\", ".time().", \"".addslashes($refer)."\")",$db) or die("loldead1");
	if ($_GET['state']!="stop") {
		$vidsize = @mysql_result(mysql_query("SELECT size FROM ac_vids WHERE vidid = \"".mysql_escape_string($videoId)."\""),0);
		if ($vidsize == false) exit("NO SIZE"); //NO SUCK VID!!
		
		if ($quality == "hq") {
			$mirrorid = mysql_query("SELECT mirrorid FROM ac_ipmap WHERE ip = \"".mysql_escape_string($_SERVER['REMOTE_ADDR'])."\" AND vidid = \"".addslashes($videoId)."\"");
			$mirrorid = @mysql_result($mirrorid,0);
			if ($mirrorid == false) $mirrorid = 4;
		} else {
			$mirrorid = 4;
		}
	
		mysql_unbuffered_query("UPDATE ac_mirrors SET estband = estband + {$vidsize} WHERE id = {$mirrorid}") or die("loldead4");
		mysql_unbuffered_query("UPDATE ac_vids SET views = views + 1, viewsmonth = viewsmonth + 1 WHERE vidid = \"".addslashes($videoId)."\"",$db) or die("loldead5");
	}
}

?>