<?php

require_once("http_socket.php");

function httpCacheSocket($url,$cacheId=false) {
	if (!$cacheId) $cacheId = md5($url);
	$cacheFile = "httpcache/".$cacheId;
	
	if (file_exists($cacheFile)) {
		$cacheData = unserialize(file_get_contents($cacheFile));
		
		if (!is_array($cacheData)) {
			unset($cacheData);
		} else if ($cacheData['expire'] > time()) {
			return $cacheData['content'];
		}
	}
	
	$httpData = http_socket($url);

	if (strpos($httpData,"HTTP:") === 0 || $httpData == null || is_array($httpData)) {
		//No data or ERROR
		if (!isset($cacheData)) {
			$cacheData = array("content"=>false);
		}
		
		$cacheData['expire'] = time() + 900; //So things don't go slow as heck when a feed is down
	} else {
		//Got it and such, stuff it in cache
		$cacheData = array("content"=>$httpData,"expire"=>time()+3600);
	}
	
	//Store in cache
	@file_put_contents($cacheFile,serialize($cacheData));

	//And return
	return $cacheData['content'];
}

?>