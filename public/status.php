<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>I'ma stateus repot!</title>
<meta http-equiv="refresh" content="15" />
<link rel="stylesheet" href="img/style.css" type="text/css" media="all" title="CTV" />
</head>
<body>
<?php
//status
$id = str_replace(".","",escapeshellcmd($_GET['id']));
if (!file_exists("../encodestore/cap{$id}.log")) {
	if (!file_exists("../encodestore/cap{$id}.cap")) {
		echo "Could not find that video id! It may have been moved to a different server.";
	} else {
		$files = glob("../encodestore/*.cap",GLOB_NOSORT);
		usort($files,"fileSorter");
		foreach ($files as $tid => $file) {
			if ($file == "../encodestore/cap{$id}.cap") break;
		}
		$tid++;
		if ($tid == 1) {
			echo "Waiting for Encoder...";
		} else {
			echo "Video is in queue. Your video is in position {$tid} of ".count($files).".";
		}
	}
} else {
	$file = file_get_contents("../encodestore/cap{$id}.log");
	echo str_replace(array("\n","%vidplayer%"),array("<br />","<a href=\"/{$id}\" target=\"_top\">View it here</a>"),$file);
}

function fileSorter($a, $b) {
	return filemtime($a) > filemtime($b);
}
?>
</body>
</html>
