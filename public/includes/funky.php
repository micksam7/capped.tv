<?php
/**
 * Funky.php
 * Capped.TV Functions
 *
 * @author micksam7
 */


//Set some default stuff
//set_error_handler("error");
date_default_timezone_set("America/New_York"); // this should be taken care of in custom php ini..
//session_save_path("../.capped.private/"); // Taken care of in custom php.ini

include_once("../capped.private.php");


/**
 * Gets a database connection
 *
 * @return mySQL Pointer
 */
function getdatabase() {
	$db = @mysql_pconnect("localhost",CAPPED_DB_LOGIN,CAPPED_DB_PASSWORD) or error(E_ERROR,"DB Could not connect");
	mysql_select_db(CAPPED_DB_NAME,$db) or error(E_ERROR,"DB is unaccessable");
	query("SET NAMES 'utf8'",$db);
	return $db;
}

/**
 * Error Handler, clears cache and exits with error, or adds one
 *
 * @param int $num Error Bitfield
 * @param string $str Error String
 * @param string $file Error File
 * @param string $line Error Line
 * @return void
 */
function error($num,$str,$file,$line) {
	if ($num == E_PARSE || $num == E_ERROR) {
		@ob_clean();
		$error = <<< ERRORTEMPLATE
		<html>
		<head>
		<title>ANOTHER ERROR?! Where's the bug spray!?</title>
		</head>
		<body>
		<h1>Critical Error Occured</h1><br />
		<b>Debug:</b> {$str}
		</body>
		</html>
ERRORTEMPLATE;
		exit($error);
	} else {
		$file = array_pop(explode("/",str_replace("\\","/",$file)));
		echo "<span><b>An Error Occured:</b> {$file}@{$line}: {$str} </span>";
	}
}

/**
 * Queries the database
 *
 * @param string $query
 * @param pointer $dbin
 * @return mySQL result pointer
 */
function query($query,$dbin=null) {
	if ($dbin == null) { global $db; $dbin = $db; }
	$result = mysql_query($query,$dbin) or error(E_ERROR,"An mySQL query error occured while processing your request.");
	return $result;
}

/**
 * Turns text into Html Entities of course!
 *
 * @param string $text
 * @return string
 */
function he($text) {
	return htmlspecialchars($text, ENT_NOQUOTES, "UTF-8");
}

/**
 * Turns text into HTML Entities of course! Changes by reference
 *
 * @param string $text
 * @return string
 */
function her(&$text) {
	$text = htmlspecialchars($text, ENT_NOQUOTES, "UTF-8");
	return $text;
}

/**
 * Changes lenght in seconds to a readable time stamp in h:m:s format, truncated to the most significant digit.
 *
 * @param int $val Time in seconds
 * @return string New timestamp
 */
function lengthTime($val) {
	$seconds = ceil($val);
	$minutes = floor($seconds / 60);
	$seconds %= 60;
	$hours = floor($minutes / 60);
	$minutes %= 60;
	$time = "";
	if ($hours != 0) {
		$minutes = padup($minutes);
		$seconds = padup($seconds);
		$time = "{$hours}:{$minutes}:{$seconds}";
	} else if ($minutes != 0) {
		$seconds = padup($seconds);
		$time = "{$minutes}:{$seconds}";
	} else {
		$time = "{$seconds} seconds";
	}
	return $time;
}

/**
 * Pads numbers with 0s if required to make a string of two characters
 *
 * @param int $in Number in
 * @return string Padded number out
 */
function padup($in) {
	if (strlen($in) < 2) return "0{$in}";
	return $in;
}

/**
 * Finds the width and height elements from a getid3 result array
 *
 * @param array $array getid3 Array
 * @return array width and height in array form
 */
function findWidthHeight($array) {
	$width = null;
	$height = null;
	foreach ($array as $id => $value) {
		if (is_array($value)) list($width,$height) = findWidthHeight($value);
		if (strpos(strtolower($id),"width") !== false && is_numeric($value)) $width = $value;
		if (strpos(strtolower($id),"height") !== false && is_numeric($value)) $height = $value;
		if ($width != null && $height != null) break;
	}
	if ($width == null || $height == null) return array(null, null);
	return array($width, $height);
}

/**
 * Creates a bat file of console commands and then executes it at low priority.
 * Be sure to properly escape and/or eat any user-input.
 *
 * @param string $cmd Command[s] to execute
 * @return array Return of the command[s] executed
 */
function startit($cmd) {
	$stamp = time().".bat";
	file_put_contents($stamp,$cmd." 2>&1\nexit");
	//$exec = array();
	$exec = shell_exec("start /low /b {$stamp}");
	//$exec .= file_get_contents("{$stamp}.txt");
	//echo strlen($exec);
	$exec = explode("\n",$exec);
	unlink($stamp);
	//unlink($stamp.".txt");
	return $exec;
}

/**
 * Gets infomation from a capped NFO file for a video, for DB-less nodes and scripts
 *
 * @param string $vid Capped VID
 * @return array Array of video infomation
 */
function getinfo($vid) {
	$nfo = file_get_contents("nfo/{$vid}.cap");
	$comment = str_replace("\r","",$nfo); //Mmm.. windows formatting
	if (strpos($comment,"\n[") !== false) {
		$comment = substr($comment,strpos($comment,"\n[")+2);
		$comment = substr($comment,0,strpos($comment,"]\n"));
	} else $comment = null;
	$nfo = explode("\n",$nfo);
	$data = array("comment"=>htmlentities($comment, ENT_COMPAT, "UTF-8"));
	try {
		require_once("getid3/getid3.php");
		$getid3 = new getID3;
		$getid3 = $getid3->Analyze("flv/{$vid}.flv");
		list($width,$height) = findWidthHeight($getid3);
		$data['width'] = $width;
		$data['height'] = $height;
		$data['length'] = $getid3['playtime_seconds'];
		$data['bitrate'] = round(filesize("flv/{$vid}.flv") / $getid3["playtime_seconds"] / 1024 * 8);
	} catch (Exception $e) { exit("There was a error in the fruit processing script. So things broke and the server isn't happy. I suggest you run away. {$e}"); }
	foreach ($nfo as $line) {
		if (($pos = strpos($line,":")) !== false && substr($line,0,$pos) == strtoupper(substr($line,0,$pos))) {
			$data[htmlentities(strtolower(substr($line,0,$pos)), ENT_COMPAT, "UTF-8")] = htmlentities(trim(substr($line,$pos+2)), ENT_COMPAT, "UTF-8");
		}
	}
	return $data;
}

/**
 * Gets a user's login data;
 * Throws a Exception if it's not valid or if they aren't logged in
 *
 * @throw Exception
 * @return array Of user data
 */
function getLogin() {
	session_start();
	require_once "includes/sceneid.php";
	require_once "../capped.private.php";
	
	if (!isset($_SESSION['sceneid_uid'])) {
		$loggedin = false;
	} else {
		$data = SceneId::Factory(SCENEID_LOGIN, SCENEID_PASSWORD)->getUserInfoById($_SESSION['sceneid_uid'])->asSimpleXML();
		if ($data->returnCode != 10) {
			throw new Exception("Bad userid info.");
		}
		$_SESSION['sceneid_uid'] = (int) $data->user->id;
		$data = $data->user;
		$loggedin = true;
	}

	if (!$loggedin) throw new Exception("You need to log in first!");

	return $data;
}

/**
 * Forms a VID from a author and title, removing dupe stuff
 *
 * @param string $string Author and title input
 * @return string VID Output
 */
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

/**
 * HTTP Cache Socket, Gets a URL and stuff, caches for a hour
 *
 * @param string $url Full HTTP Url
 * @param string $cacheId ID for the cache file
 * @return string Result File
 */
function httpCacheSocket($url,$cacheId=false,$expire=3600) {
	require_once("http_socket.php");

	if (!$cacheId) $cacheId = md5($url);
	$cacheFile = "httpcache/".$cacheId;

	if (file_exists($cacheFile)) {
		$cacheData = unserialize(file_get_contents($cacheFile));

		if (!is_array($cacheData)) {
			unset($cacheData);
		} else if ($cacheData['time'] + $expire > time()) {
			return $cacheData['content'];
		}
	}

	$httpData = http_socket($url);

	if (strpos($httpData,"HTTP:") === 0 || $httpData == null || is_array($httpData)) {
		//No data or ERROR
		if (!isset($cacheData)) {
			$cacheData = array("content"=>false);
		}

		$cacheData['time'] = time(); //So things don't go slow as heck when a feed is down
	} else {
		//Got it and such, stuff it in cache
		$cacheData = array("content"=>$httpData,"time"=>time());
	}

	//Store in cache
	@file_put_contents($cacheFile,serialize($cacheData));

	//And return
	return $cacheData['content'];
}

/**
 * Sorts files by file creation time, callback for usort
 *
 * @param string $a File a to compare to...
 * @param string $b File b
 * @return bool
 */
function fileSorter($a, $b) {
	return filemtime($a) > filemtime($b);
}

/**
 * Grabs nearest avalible mirror and sets up ip->mirror linking
 *
 * @param string $vid Video ID for the mirror
 * @param string $db Databasen
 * @return string FISHY!
 */
function getMirror($vid) {
	return array("name"=>"Capped.TV CDN","path"=>"http://cdn.capped.tv/");

	//Who has it?!
	$query = mysql_query("SELECT mirrorid FROM ac_mirrorsync WHERE vidid = \"".mysql_escape_string($vid)."\" AND valid = 1");
	$haveFile = array(1=>true,4=>true); //Capped.TV root, and CDN always have files
	while ($result = mysql_fetch_array($query)) {
		$haveFile[$result['mirrorid']] = true;
	}
	
	//And then we'll see if they actually wanna run them on the mirror
	$query = mysql_query("SELECT * FROM ac_mirrors WHERE enabled = 1 AND estband + actband < bandlimit");
	$availableMirrors = array();
	$mirrors = array();
	while ($result = mysql_fetch_array($query)) {
		$mirrors[$result['id']] = $result;
		if (isset($haveFile[$result['id']])) {
			$availableMirrors[$result['id']] = $result;
		}
	}

	if (count($haveFile) == 0) return $mirror[4]; //CDN

	//Alright, check cache.. yes just now.. lol..
	$query = mysql_query("SELECT mirrorid FROM ac_ipmap WHERE vidid = \"".mysql_escape_string($vid)."\" AND ip = \"".mysql_escape_string($_SERVER['REMOTE_ADDR'])."\"");
	if (mysql_num_rows($query) > 0 && $mirror = @mysql_result($query,0)) {
		if (isset($availableMirrors[$mirror])) {
			return $mirrors[$mirror];
		}
	}
	
	//Only one has it!
	if (count($availableMirrors) == 1) {
		$selectedMirror = array_pop($availableMirrors);
		setMirror($_SERVER['REMOTE_ADDR'],$selectedMirror['id'],$vid);
		return $selectedMirror;
	}
	
	//Geo IP stuff
	$userLocation = geoIp($_SERVER['REMOTE_ADDR']);

	if (!$userLocation) return "http://cdn.capped.tv/";
	
	//Distance checking
	$distances = array();
	foreach ($availableMirrors as $id => $mirror) {
		if ($mirror['name'] == "CDN") { $distances[$id] = 600; continue; }
		$distances[$id] = globalDistance($userLocation,$mirror);
	}
	
	asort($distances);

	//Check to see if any are within 100 rads of each other, put em in a list
	$selected = $distances;
	$key = array_shift($distances);
	foreach ($selected as $id => $distance) {
		if ($key + 100 < $distance) unset($selected[$id]);
	}
	
	//Then select a random one of that list
	$selectedMirror = $availableMirrors[array_rand($selected)];

	setMirror($_SERVER['REMOTE_ADDR'],$selectedMirror['id'],$vid);
	
	return $selectedMirror;
}

/**
 * Sets a server for a ip and a video
 *
 * @param string $ip IP to set on
 * @param interger $mirror Mirror ID
 * @param string $vid Video ID
 * @return void
 */
function setMirror($ip,$mirror,$vid) {
	//mysql_query("REPLACE INTO ac_ipmap (ip, vidid, mirrorid, date) VALUES (\"".mysql_escape_string($ip)."\", \"".mysql_escape_string($vid)."\", {$mirror}, ".time().")") or die("LOLDEAD");
}

/**
 * GEOIP LOOKUP!!1 Requires database
 * 
 * @param string $ip IPv4 IP
 * @param handle $db DB!
 * @return array Latitude and Longitude!
 */
function geoIp($ip) {
	if (($ip=ip2long($ip)) < 0){ $ip += 4294967296 ;}
	
	//Find the location ID
	$query = mysql_query("SELECT locationid FROM geoip_ips WHERE {$ip} >= startip AND {$ip} <= endip");
	if (mysql_num_rows($query) == 0 || ($location = @mysql_result($query,0)) == false || $location == 1) return false;
	
	//Find the coords of the id
	$query = mysql_query("SELECT * FROM geoip_locations WHERE id = {$location}");
	$location = mysql_fetch_array($query);
	
	if ($location == false || ($location['latitude'] == 0 && $location['longitude'] == 0)) return false;

	return $location;
}

/**
 * Calculates distance between two coordinates on the globe!
 * Kindly adapted from http://stackoverflow.com/questions/407989/calculating-distance-between-zip-codes-in-php
 *
 * @param array First point! latitude, longitude
 * @param array Second point! latitude, longitude
 * @return float Distance!!1 Degrees in radians
 */
function globalDistance($point1, $point2)
{
    $radius      = 3958;      // Earth's radius (miles)
    $pi          = 3.1415926;
    $deg_per_rad = 57.29578;  // Number of degrees/radian (for conversion)

    $distance = ($radius * $pi * sqrt(
                ($point1['latitude'] - $point2['latitude'])
                * ($point1['latitude'] - $point2['latitude'])
                + cos($point1['latitude'] / $deg_per_rad)  // Convert these to
                * cos($point2['latitude'] / $deg_per_rad)  // radians for cos()
                * ($point1['longitude'] - $point2['longitude'])
                * ($point1['longitude'] - $point2['longitude'])
        ) / 180);

    return $distance; // Returned using the units used for $radius.
}

/**
 * Grabs FPS, Width, Height, and Time of a video, uses ffmpeg
 *
 * @param string $file File to video to check
 * @return array fps, width, height, time
 */
function getVideoInfo($file) {
	$script = startit("\"./encoder/ffmpeg\" -i {$file}");
	$script = implode("\n",$script);
	//var_dump($script);
	if (!preg_match("/Video: .* (\d+)x(\d+)/",$script,$size)) return false;
	if (!preg_match("/Video: .* (\d+(?:\.\d+)?) fps/",$script,$fps) && !preg_match("/framerate .* : (\d+(?:\.\d+)?)/",$script,$fps) && !preg_match("/Video: .* (\d+(?:\.\d+)?) tbc/",$script,$fps)) return false;
	if (!preg_match("/Duration: (\d+):(\d\d):(\d\d)\.(\d+)/",$script,$length)) return false;
	
	$time = $length[1] * 3600;
	$time += $length[2] * 60;
	$time += ($length[3] . "." . $length[4]);
	
	return array("fps"=>$fps[1],"width"=>$size[1],"height"=>$size[2],"time"=>$time);
}

?>