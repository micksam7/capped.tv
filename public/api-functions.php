<?php
/**
 * Capped.TV's Bath Water
 * 
 * API Functions used in SOAP, XML, Flash Var, Plain Text, and even normal pages here.
 * See other api-* files.
 * 
 * @author The Magic Dolphin [micksam7.com] 
 */

@header("Content-Type: text/html; charset=UTF-8");

$ctvApiHasMore = false;

//Random other scripts grab functions and this, so keep this _once please.
require_once "includes/funky.php";

/**
 * Link Varible, class for SOAP/API
 */
class linkVar {
  var $name;
  var $url;
  function linkVar($name, $url) {
    $this->name = $name;
    $this->url = $url;
  }
}

/**
 * Video Varible, class for SOAP/API
 */
class vidVar {
	var $id, $name, $author, $width, $height, $bitrate, $postTime, $views, $monthViews,
	$uploader, $uploaderId, $comment, $links, $videoLink, $flv6Link, $imageLink, $audioLink,
	$nfoLink, $size, $length, $vidLQ, $vidMQ, $vidHQ, $mirrorInfo, $realId, $quality, $thumb, $pouetId;
	function vidVar() {}
	function setVar($name, $var) {
		$this->$name = $var;
	}
}

/**
 * Stats Varible, class for SOAP/API
 */
class statsVar {
	var $totalVids, $totalViews, $monthViews;
	function statsVar() {}
	function setVar($name, $var) {
		$this->$name = $var;
	}
}

/**
 * Parses a array of data from a query to a vidVar struct
 *
 * @param string $buildin VID for a capped video
 * @return vidVar
 */
function buildVideoStruct($buildin, $quality = "hq") {
	$buildout = array();
	$vid = $buildin['vidid'];
	
	//Grab the links out of our static file
	if (!file_exists("nfo/{$vid}.cap")) return array();
	$nfo = @file_get_contents("nfo/{$vid}.cap");
	$nfo = explode("\n",$nfo);
	$links = array();
	foreach ($nfo as $line) {
		if (($pos = strpos($line,":")) !== false && substr($line,0,$pos) == strtoupper(substr($line,0,$pos))) {
			$text = trim(substr($line,0,$pos));
			$info = trim(substr($line,$pos+1));
			if ($text == "REDIRECT") {
				return array();
			}
			if ($text == "UPLOADER" || $text == "TITLE" || $text == "BY" || trim($info) == null) continue;
			$tmp = new linkVar(ucfirst(strtolower($text)),$info);
			$links[] = $tmp;
		}
	}

	//mySQL => struct name
	$layout = array('id'         => 'vidid',
					'name'       => 'title',
					'author'     => 'author',
					'width'      => 'width',
					'height'     => 'height',
					'bitrate'    => 'bitrate',
					'size'	     => 'size',
					'postTime'   => 'date',
					'length'     => 'length',
					'views'      => 'views',
					'monthViews' => 'viewsmonth',
					'uploader'   => 'uploader_name',
					'uploaderId' => 'uploader_id',
					'comment'    => 'comment',
					'pouetId'    => 'pouetid'
	);
	
	//Convert mySQL to the struct/array
	foreach ($layout as $out => $in) { if (isset($buildin[$in]) && $buildin[$in] != null && (!is_numeric($buildin[$in]) || $buildin[$in] != 0)) $buildout[$out] = $buildin[$in]; }
	
	if (!isset($buildout['uploaderId'])) {
		$buildout['uploader'] = "Capped.TV";
		$buildout['uploaderId'] = 0;
	}
	
	$buildout['links'] = $links;
	
	//Get server for links
	$server = getMirror($vid); //Gets only mirrors confirmed to have that video
	$buildout['mirrorInfo'] = $server;
	$server = $server['path'];
	
	//Now video links
	if (file_exists("vhq/{$vid}.mp4")) {
		$buildout['vidHQ'] = array(
			"link"=>"http://cdn.capped.tv/vhq/{$vid}.mp4",
			"width"=>$buildin['width'],
			"height"=>$buildin['height'],
			"bitrate"=>$buildin['bitrate'],
			"size"=>$buildin['size'],
			);
	}
	if (file_exists("vmq/{$vid}.mp4") && $buildin['mqSize'] != null) {
		$buildout['vidMQ'] = array(
			"link"=>"http://cdn.capped.tv/vmq/{$vid}.mp4",
			"width"=>$buildin['mqWidth'],
			"height"=>$buildin['mqHeight'],
			"bitrate"=>$buildin['mqBitrate'],
			"size"=>$buildin['mqSize'],
			);
	}
	if (file_exists("vlq/{$vid}.flv") && $buildin['lqSize'] != null) {
		$buildout['vidLQ'] = array(
			"link"=>"http://cdn.capped.tv/vlq/{$vid}.flv",
			"width"=>$buildin['lqWidth'],
			"height"=>$buildin['lqHeight'],
			"bitrate"=>$buildin['lqBitrate'],
			"size"=>$buildin['lqSize'],
			);
	}
	
	//Get primary
	//If one isn't avalible, it goes down to the next ;)
	switch ($quality) {
		case 'hq':
		if (isset($buildout['vidHQ'])) {
			$buildout['videoLink'] = $buildout['vidHQ']['link'];
			$buildout['width']     = $buildout['vidHQ']['width'];
			$buildout['height']    = $buildout['vidHQ']['height'];
			$buildout['size']      = $buildout['vidHQ']['size'];
			$buildout['bitrate']   = $buildout['vidHQ']['bitrate'];
			break;
		}
		case 'mq':
		if (isset($buildout['vidMQ'])) {
			$buildout['videoLink'] = $buildout['vidMQ']['link'];
			$buildout['width']     = $buildout['vidMQ']['width'];
			$buildout['height']    = $buildout['vidMQ']['height'];
			$buildout['size']      = $buildout['vidMQ']['size'];
			$buildout['bitrate']   = $buildout['vidMQ']['bitrate'];
			$buildout['mirrorInfo'] = false;
			break;
		}
		case 'lq':
		if (isset($buildout['vidLQ'])) {
			$buildout['videoLink'] = $buildout['vidLQ']['link'];
			$buildout['width']     = $buildout['vidLQ']['width'];
			$buildout['height']    = $buildout['vidLQ']['height'];
			$buildout['size']      = $buildout['vidLQ']['size'];
			$buildout['bitrate']   = $buildout['vidLQ']['bitrate'];
			$buildout['mirrorInfo'] = false;
			break;
		}
		default:
			return array();
	}
	
	$buildout['imageLink'] = "/jpg/{$vid}.jpg";
	
	if (file_exists("mp3/{$vid}.mp3")) $buildout['audioLink'] = "http://cdn.capped.tv/mp3/{$vid}.mp3";

	if (file_exists("vlq/{$vid}.flv")) $buildout['flv6Link'] = "http://cdn.capped.tv/vlq/{$vid}.flv";
	
	$buildout['nfoLink'] = "/nfo/{$vid}.cap";

	$buildout['realId'] = $buildout['id'];

	$buildout['quality'] = $quality;
	
	$buildout['thumb']['image'] = $buildout['imageLink'];
	
	if (file_exists("pre/{$vid}.png")) $buildout['thumb']['image'] = "http://cdn.capped.tv/pre/{$vid}.png";
	if (file_exists("pre/{$vid}.mp4")) $buildout['thumb']['videos']['mp4'] = "http://cdn.capped.tv/pre/{$vid}.mp4";
	if (file_exists("pre/{$vid}.ogv")) $buildout['thumb']['videos']['ogv'] = "http://cdn.capped.tv/pre/{$vid}.ogv";
	if (file_exists("pre/{$vid}.webm")) $buildout['thumb']['videos']['webm'] = "http://cdn.capped.tv/pre/{$vid}.webm";

	$buildout['id'] = $buildout['id'] . "|" . $quality;

	//Shove it into a vidVar for SOAP
	$build = new vidVar();

	foreach ($buildout as $object => $value) {
		$build->setVar($object,$value);
	}
	
	return $build;
}

/**
 * Gets a video by it's ID from the database
 *
 * @param string $videoId Capped.TV VID
 * @return vidVar
 */
function getVidById($videoId=0) {
	$db = getdatabase();
	if ($db == false) return array();
	
	if (strpos($videoId,"|") !== false) {
		list($videoId,$quality) = explode("|",$videoId);
		if ($quality != "hq" && $quality != "mq" && $quality != "lq") {
			$quality = "hq";
		}
	} else {
		$quality = "hq";
	}
	
	$videoId = mysql_real_escape_string($videoId);
	$vid = query("SELECT * FROM ac_vids WHERE vidid = \"{$videoId}\"",$db);

	if ($vid == false || mysql_num_rows($vid) == 0) return array();

	$vid = mysql_fetch_assoc($vid);

	return buildVideoStruct($vid, $quality);
}

/**
 * Gets all videos for a specific Pouet ID
 *
 * @param int $pouetId Pouet Prod ID
 * @return array of vidId
 */
function findVidsByPouetId ($pouetId=0, $limit=15, $offset=false) {
	$db = getdatabase();
	if ($db == false) return array();
	$pouetId = mysql_real_escape_string($pouetId);
	$offset = $offset !== false ? "OFFSET " . ((int) $offset) : "";
	$limit = (int) $limit + 1;
	$vid = query("SELECT * FROM ac_vids WHERE pouetid = \"{$pouetId}\" ORDER BY pouetid LIMIT {$limit} {$offset}",$db);

	if ($vid == false || mysql_num_rows($vid) == 0) return array();
	
	$buildOut = array();
	
	while ($info = mysql_fetch_assoc($vid)) {
		$info = buildVideoStruct($info);
		if ($info == false) continue;
		$buildOut[] = $info;
	}
	
	if (count($buildOut) >= $limit) {
		global $ctvApiHasMore;
		$ctvApiHasMore = true;
		array_pop($buildOut);
	} else {
		global $ctvApiHasMore;
		$ctvApiHasMore = false;
	}

	return $buildOut;
}

/**
 * Gets all videos for a specific User Scene ID
 *
 * @param int $userId Scene ID #
 * @return array of vidId
 */
function findVidsByUserId ($userId=0, $limit=15, $offset=false) {
	$db = getdatabase();
	if ($db == false) return array();
	$userId = mysql_real_escape_string($userId);
	$limit = (int) $limit + 1;
	$offset = $offset !== false ? "OFFSET " . ((int) $offset) : "";
	$vid = query("SELECT * FROM ac_vids WHERE uploader_id = \"{$userId}\" ORDER BY date DESC LIMIT {$limit} {$offset}",$db);

	if ($vid == false || mysql_num_rows($vid) == 0) return array();
	
	$buildOut = array();
	
	while ($info = mysql_fetch_assoc($vid)) {
		$info = buildVideoStruct($info);
		if ($info == false) continue;
		$buildOut[] = $info;
	}
	
	if (count($buildOut) >= $limit) {
		global $ctvApiHasMore;
		$ctvApiHasMore = true;
		array_pop($buildOut);
	} else {
		global $ctvApiHasMore;
		$ctvApiHasMore = false;
	}

	return $buildOut;
}

/**
 * Gets -all- videos
 *
 * @return array of vidId
 */
function getAllVids ($limit=false, $offset=false, $reverse=false) {
	$db = getdatabase();
	if ($db == false) return array();
	
	$order = $reverse ? "DESC" : "ASC"; 
	$offset = $offset !== false ? "OFFSET " . ((int) $offset) : "";
	$limit = (int) $limit + 1;
	$vid = query("SELECT * FROM ac_vids ORDER BY lower(author) {$order}, lower(title) {$order} LIMIT {$limit} {$offset}",$db);

	if ($vid == false || mysql_num_rows($vid) == 0) return array();
	
	$buildOut = array();
	
	while ($info = mysql_fetch_assoc($vid)) {
		$info = buildVideoStruct($info);
		if ($info == false) continue;
		$buildOut[] = $info;
	}
	
	if (count($buildOut) >= $limit) {
		global $ctvApiHasMore;
		$ctvApiHasMore = true;
		array_pop($buildOut);
	} else {
		global $ctvApiHasMore;
		$ctvApiHasMore = false;
	}

	return $buildOut;
}

/**
 * Gets latest videos
 *
 * @param int $num Limit to....?
 * @return array of vidId
 */
function getLatestVids ($num=15, $offset=false, $reverse=false) {
	$db = getdatabase();
	if ($db == false) return array();
	$num = (int) $num + 1;
	$order = $reverse ? "ASC" : "DESC";
	$offset = $offset !== false ? "OFFSET " . ((int) $offset) : "";
	$vid = query("SELECT * FROM ac_vids ORDER BY date {$order} LIMIT {$num} {$offset}",$db);

	if ($vid == false || mysql_num_rows($vid) == 0) return array();
	
	$buildOut = array();
	
	while ($vidInfo = mysql_fetch_assoc($vid)) {
		$info = buildVideoStruct($vidInfo);
		if ($info == false) { 
			trigger_error("CAPPED no data files for vid {$vidInfo['vidid']}",E_USER_NOTICE);
			$num--;
			continue;
		}
		$buildOut[] = $info;
	}
	
	if (count($buildOut) >= $num) {
		global $ctvApiHasMore;
		$ctvApiHasMore = true;
		array_pop($buildOut);
	} else {
		global $ctvApiHasMore;
		$ctvApiHasMore = false;
	}

	return $buildOut;
}

/**
 * Gets top videos
 *
 * @param int $num Limit to....?
 * @return array of vidId
 */
function getTopVids ($num = 15, $offset=false, $reverse=false) {
	$db = getdatabase();
	if ($db == false) return array();
	$num = (int) $num + 1;
	$order = $reverse ? "ASC" : "DESC";
	$offset = $offset !== false ? "OFFSET " . ((int) $offset) : "";
	$vid = query("SELECT * FROM ac_vids ORDER BY views {$order} LIMIT {$num} {$offset}",$db);

	if ($vid == false || mysql_num_rows($vid) == 0) return array();
	
	$buildOut = array();
	
	while ($info = mysql_fetch_assoc($vid)) {
		$info = buildVideoStruct($info);
		if ($info == false) continue;
		$buildOut[] = $info;
	}
	
	if (count($buildOut) >= $num) {
		global $ctvApiHasMore;
		$ctvApiHasMore = true;
		array_pop($buildOut);
	} else {
		global $ctvApiHasMore;
		$ctvApiHasMore = false;
	}

	return $buildOut;
}

/**
 * Gets bottom videos
 *
 * @param int $num Limit to....?
 * @return array of vidId
 */
function getBottomVids ($num = 15, $offset=false, $reverse=false) {
	getTopVids($num,$offset,!$reverse);
}

/**
 * Gets top of the month videos
 *
 * @param int $num Limit to....?
 * @return array of vidId
 */
function getTopMonthVids ($num=15, $offset=false, $reverse=false) {
	$db = getdatabase();
	if ($db == false) return array();
	$num = (int) $num + 1;
	$order = $reverse ? "ASC" : "DESC";
	$offset = $offset !== false ? "OFFSET " . ((int) $offset) : "";
	$vid = query("SELECT * FROM ac_vids ORDER BY viewsmonth {$order} LIMIT {$num} {$offset}",$db);

	if ($vid == false || mysql_num_rows($vid) == 0) return array();
	
	$buildOut = array();
	
	while ($info = mysql_fetch_assoc($vid)) {
		$info = buildVideoStruct($info);
		if ($info == false) continue;
		$buildOut[] = $info;
	}
	
	if (count($buildOut) >= $num) {
		global $ctvApiHasMore;
		$ctvApiHasMore = true;
		array_pop($buildOut);
	} else {
		global $ctvApiHasMore;
		$ctvApiHasMore = false;
	}

	return $buildOut;
}

/**
 * Gets general Capped.TV Statistics
 *
 * @return statsVar
 */
function getStatistics() {
	$db = getdatabase();
	if ($db == false) return array();
	
	$stats = new statsVar();
	
	$vids = query("SELECT sum(views) FROM ac_vids;",$db);
	$stats->totalViews = number_format(mysql_result($vids,0));
	$vids = query("SELECT sum(viewsmonth) FROM ac_vids;",$db);
	$stats->monthViews = number_format(mysql_result($vids,0));
	$vids = query("SELECT COUNT(*) FROM ac_vids",$db);
	$stats->totalVids = number_format(mysql_result($vids,0));
	
	return $stats;
}

/**
 * Checks if a limited and offseted function has more data
 * 
 * @return boolean
 */
function hasMore() {
	global $ctvApiHasMore;
	return $ctvApiHasMore;
}

function search($search,$limit=15,$offset=false) {
		//Pre-process Query
		$search = strtolower(str_replace(array("AND","OR"),"",$search));

		//Split Query
		$search = explode(" ",$search);
		
		$db = getdatabase();

		//Process Query
		$words = array();
		$quote = array();
		foreach ($search as $id => $element) {
			if (count($quote) > 0) {
				if (strpos($element,'"') !== false) {
					$element = str_replace('"',"",$element);
					$quote[] = $element;
					$words[] = implode(" ",$quote);
					$quote = array();
				} else $quote[] = $element;
			}
			else if ($element == "&") {}
			else if ($element == "") {}
			else if (strpos($element,'"') === 0) {
				if (strpos($element,'"',1) === false) {
					$quote = array(substr($element,1));
				} else {
					$words[] = str_replace('"',"",$element);
				}
			} else $words[] = $element;
		}
		
		if (count($quote) > 0) {
			$words[] = implode(" ",$quote);
		}

		//Process Words
		$query = array();
		foreach ($words as $word) {
			if ($word == "") continue; //?!
			$word = mysql_real_escape_string($word);
			$query[] = "title LIKE \"%{$word}%\" OR author LIKE \"%{$word}%\" OR uploader_name LIKE \"%{$word}%\" OR comment LIKE \"%{$word}%\"";
		}

		$query = implode(" OR ",$query);
		
		if ($query == "") return array();

		$vids = query("SELECT * FROM ac_vids a WHERE {$query} ORDER BY views DESC",$db);

		$results = array();
		$ranking = array();

		if (mysql_num_rows($vids) < 1) {
			return array();
		} else {

			while ($vid = mysql_fetch_assoc($vids)) {
				$results[$vid['vidid']] = $vid;
				$ranking[$vid['vidid']] = (int) ("0.".$vid['views']);
				foreach ($words as $word) {
					if ($word == "") continue; //?!
					if (strpos(strtolower($vid['author']),$word) !== false) {
						$ranking[$vid['vidid']] += 30;
					}
					if (strpos(strtolower($vid['title']),$word) !== false) {
						$ranking[$vid['vidid']] += 20;
					}
					if (strpos(strtolower($vid['uploader_name']),$word) !== false) {
						$ranking[$vid['vidid']] += 10;
					}
					if (strpos(strtolower($vid['comment']),$word) !== false) {
						$ranking[$vid['vidid']] += 5;
					}
				}
			}

			$avg = array_sum($ranking) / count($ranking);

			asort($ranking);
			$ranking = array_reverse($ranking,true);

			$buildOut = array();
			$t = 0;
			if ($offset == false) $offset = 0;
			$limit += $offset;
			
			global $ctvApiHasMore;
			$ctvApiHasMore = false;
			
			foreach ($ranking as $id => $info) {
				$t++;
				if ($t < $offset) continue;
				if ($t > $limit) {
					$ctvApiHasMore = true;
					break;
				}
				$info = buildVideoStruct($results[$id]);
				if ($info == false) continue;
				$buildOut[] = $info;
			}
		}
		return $buildOut;
}

?>