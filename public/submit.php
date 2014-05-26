<?php header("Content-Type: text/html; charset=utf-8", true); ?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<style>
body {
	margin: 0px;
	background: #ffffff;
}

* {
	font-family: Tahoma, Ariel;
	font-size: 12px;
	text-decoration: none;
}

table {
	border: 1px solid black;
	padding: 0px;
	min-height: 20px;
}

td {
	padding: 3px 10px 3px 10px;
	border: 1px solid black;
}

form {
	display: inline;
}
</style>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Capped - Juicing Your Fruit</title>
</head>
<body>
<center><img src="upload.png" alt="Capped Header" />
<table width="700">
	<tr>
		<td colspan=2><?php

		require "includes/funky.php";
		set_time_limit(86400);

		try {
			$data = getLogin();
			$loggedin = true;
			if ($data->nickname != null) {
				$user = "{$data->nickname} [{$data->id}]";
			} else {
				$user = "{$data->login} [{$data->id}]";
			}
		} catch (Exception $e) {
			$loggedin = false;
			unset($data);
		}

		if (!$loggedin) exit("You aren't logged in!");

		if ($data->login == "koreanclan") { exit("Capped.TV is only for demoscene-related videos. Please upload elsewhere."); }

		if (!isset($_POST['atitle']) || $_POST['atitle'] == null || $_POST['by'] == null || $_POST['info'] == null) {
			exit("You didn't fill out the complete form. Make sure you enter a title, a group/author, and at least 1 link.");
		}
		
		list($_POST['comment']) = explode("\n",$_POST['comment']);

		$id = formUrl($_POST['by'])."-".formUrl($_POST['atitle']);
		$t = 2;
		if (file_exists("../encodestore/cap{$id}.avi") || file_exists("nfo/{$id}.cap")) {
			while (file_exists("../encodestore/cap{$id}{$t}.avi") || file_exists("nfo/{$id}{$t}.cap")) {
				$t++;
			}
			$id = $id.$t;
		}

		if (isset($_FILES['video']) && ($_FILES['video']['size'] > 10240 || $_FILES['video']['size'] < 0)) {
			if (!move_uploaded_file($_FILES['video']['tmp_name'],"../encodestore/cap{$id}.avi")) {
				exit("Error moving file :( Possibly disk is overloaded. Report it to us capped@micksam7.com and try again in a few hours");
			}
			$grabvideo = false;
		} else if ($_POST['videourl'] != null) {
			if (!(strpos($_POST['videourl'],"http://") === 0 || strpos($_POST['videourl'],"https://") === 0 || strpos($_POST['videourl'],"ftp://") === 0)) exit("HTTP or FTP urls only! Must be http:// or ftp:// or https://");
			if (strpos($_POST['videourl'],"ftp://") !== false) { $_POST['videourl'] = urldecode($_POST['videourl']); }
			$write = fopen("../encodestore/cap{$id}.avi","w") or exit("Error downloading video. Disk may be full. Report it to us capped@micksam7.com and try again in a few hours");
			$file = fopen($_POST['videourl'],"r") or exit("Could not use URL. Is it valid? Note redirects do not work all the time.");
			echo "Downloading Video. This will take a while.\n";
			echo str_repeat(" ",1024)."<!-- these endless spaces keeps the connection alive -->".str_repeat(" ",10240)."\n";
			while ($data = fread($file,1024000)) { fwrite($write,$data); echo " "; usleep(10000); }
			fclose($file);
			fclose($write);
		} else {
			exit("No file uploaded? If you did upload a file, make sure that it's less than 1 GB. If you still get this error, report it to capped@micksam7.com and try again in a few hours");
		}

		if (isset($_POST['mp4'])&&$_POST['mp4']=="true") {
			try {
				require_once("getid3/getid3.php");
				$getid3 = new getID3;
				$getid3 = $getid3->Analyze("../encodestore/cap{$id}.avi");
				list($width,$height) = findWidthHeight($getid3);
				if (filesize("../encodestore/cap{$id}.avi") == 0 || $getid3['playtime_seconds'] == 0 || $getid3['playtime_seconds'] == null || $width == 0 || $height == 0)
					exit("Could not read uploaded mp4 file. Is it corrupted?");
			} catch (Exception $e) {
				exit("I can't read your MP4! Is it actually a valid mp4?");
			}

			if ($width > 10000 || $height > 5000) {
				exit("Width or height of MP4 is too big.");
			}

			echo "<br />Video bitrate is ".number_format(filesize("../encodestore/cap{$id}.avi") / $getid3["playtime_seconds"] / 1024 * 8,0). " kbit. Just so you know.";

			if (filesize("../encodestore/cap{$id}.avi") / $getid3["playtime_seconds"] * 8 > 8000*1024) {
				exit("And did I mention that's too big? Bitrate of straight mp4s must be under 7,500 kbit. Re-encode it please. Alternatively, uncheck MP4 option on the last page and we'll re-encode it for you.");
			}

		} else {
			$width = 640;
			$height = 480;
		}
		
		if (!isset($_FILES['image']) || $_FILES['image']['size'] == null || $_FILES['image']['size'] < 100) {
			$img = false;
		} else {
			$img = $_FILES['image']['tmp_name'];
			
			$size = getimagesize($img);
			$img = imagecreatefromstring(file_get_contents($img));
			$owidth = $size[0];
			$oheight = $size[1];
			$scale = $owidth / $width;
			if (floor($oheight / $scale) > $height) {
				$scale = $oheight / $height;
			}
			$width = ceil(floor($owidth / $scale)/2)*2;
			$height = ceil(floor($oheight / $scale)/2)*2;
			$newimg = imagecreatetruecolor($width,$height);
			imagecopyresampled($newimg,$img,0,0,0,0,$width,$height,$owidth,$oheight);
			ob_start();
			imagejpeg($newimg,null,90);
			imagedestroy($img);
			imagedestroy($newimg);
			$img = ob_get_clean();
		}

		$info = array("title"=>$_POST['atitle'],"by"=>$_POST['by'],"info"=>getinfoinput($_POST['info']),"img"=>$img,"uploader"=>$user,"comments"=>$_POST['comment'],"quality"=>-1,"ismp4"=>isset($_POST['mp4'])&&$_POST['mp4']=="true");

		file_put_contents("../encodestore/cap{$id}.cap",serialize($info));
	
		function getinfoinput($nfo) {
			$nfo = explode("\n",$nfo);
			$data = array();
			foreach ($nfo as $line) {
				if (($pos = strpos($line,":")) !== false) {
					$data[strtoupper(substr($line,0,$pos))] = trim(substr($line,$pos+2));
				}
			}
			return $data;
		}



		?> <br />
		Everything A-OK! Now you get to wait out the queue!
		</td>
	</tr>
<?php /*	<tr><td>While you are waiting, why not donate a bit of money to keep capped running?<br />
		Donations will be used for server, domain, and upgrade payments. That's about it.<br />
		Optionally, you can leave your sceneid/nickname to let us know who donated.<br />
		Please note that Paypal commision fees void any donations under 58 cents USD.
</td><td><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=928038" target="_blank"><img src="https://www.paypal.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" alt="Donate with PayPal!" /></a></td>
	</tr></td> */ ?>
	<tr>
		<td colspan=2><iframe src="/status.php?id=<?=$id ?>"
			style="width: 680px; height: 600px; border: none;"> </iframe></td>
	</tr>
</table>
</center>
</body>
</html>
<?php
/*
function formUrl($string) {
	$string = str_replace(array(" ","&"),"_",trim($string));
	$chrlist = str_split("abcdefghijklmnopqrstuvwxyz0123456789_");
	$newstr = ""; $string = strtolower($string);
	for ($t=0;$t<strlen($string);$t++) { $b = $string{$t}; if (!in_array($b,$chrlist)) $newstr .= ""; else $newstr .= $b; }
	$out = "";
	$isspace = false;
	for ($i=0;$i<strlen($newstr);$i++) {
	  if ($newstr{$i} == "_") {
	    if (!$isspace) {
	      $isspace = true;
	      $out .= "_";
	    }
	  } else {
	    $isspace = false;
	    $out .= $newstr{$i};
	  }
	}
	if (strlen($out) > 30) $out = substr($out,0,30);
	return trim($out,"_");
}

		function findWidthHeight($array) {
			$width = null;
			$height = null;
			foreach ($array as $id => $value) {
				if (is_array($value)) list($width,$height) = findWidthHeight($value);
				if (strpos(strtolower($id),"width") !== false && is_numeric($value)) $width = $value;
				if (strpos(strtolower($id),"height") !== false && is_numeric($value)) $height = $value;
				if ($width != null && $height != null) break;
			}
			if ($width == null || $height == null) return array(0, 0);
			return array($width, $height);
		}
*/
?>