<?php
$id = escapeshellcmd($_GET['vid']);
if (!file_exists("nfo/{$id}.cap")) {
	$info['info'] = "404'd";
} else {
	$info = getinfo($id);
	/*if (!isset($info['TIME'])) {
		require("getid3/getid3.php");
		$getid3 = new getID3;
		$getid3 = $getid3->Analyze("flv/{$id}.flv");
		$getid3 = $getid3['playtime_seconds'];
	} else {
		$getid3 = $info['TIME'];
		unset($info['TIME']);
	}*/

	foreach ($info as $name => $feild) {
		if ($name != "BY" && $name != "TITLE" && $name != "UPLOADER") {
			$links[] = "<u><a target=\"_blank\" href=\"".$feild."\">".ucfirst(strtolower($name))."</a></u>";
		}
	}

	$links = implode(" / ",$links);

        $links .= isset($info['UPLOADER']) ? " - Uploaded by ".substr($info['UPLOADER'],0,strrpos($info['UPLOADER'],"[")) : " - Uploaded by Capped";

	$info['info'] = <<< LALALALA
<TEXTFORMAT LEADING="2"><P ALIGN="LEFT"><FONT FACE="Impact" SIZE="10" COLOR="#FFFFFF" LETTERSPACING="0" KERNING="0">{$links}</FONT></P></TEXTFORMAT>
LALALALA;
	//$info['time'] = $getid3;
}
foreach ($info as $var => $data) {
	$info[$var] = urlencode($var) . "=" . urlencode($data);
}

echo implode("&",$info);

function getinfo($vid) {
	$nfo = file_get_contents("nfo/{$vid}.cap");
	$nfo = explode("\n",$nfo);
	$data = array();
	foreach ($nfo as $line) {
		if (($pos = strpos($line,":")) !== false && substr($line,0,$pos) == strtoupper(substr($line,0,$pos))) {
			$data[substr($line,0,$pos)] = trim(substr($line,$pos+2));
		}
	}
	return $data;
}
?>