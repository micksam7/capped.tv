<pre><?php
//Mick's HTTP Socket
//Copyright micksam7 (C) 2005-2006 [me@micksam7.com]

//Quick Usage:
/* include "http_socket.php";
 * $array = http_socket("https://example.secure.site:6070/file.htm?get=stuff",array("thisis"=>"poststuff","yep"=>"yeah"),"http://refered.from.here/",array("cookie"=>"stuff","goes"=>"here"),true); //Really complex
 * echo http_socket("mywebsite.com"); //Or really simple
 */

//string http_socket(string $site, array $post, string $referer, array $cookie, int $timeout, string $extra_headers);
//array  http_socket(string $site, array $post, string $referer, array $cookie, int $timeout, string $extra_headers, boolean $extended = true);

//Notes:
//This script does not require any non-standard external dlls or libraries or whatever [except for ssl support]. Should work on php4 and php5. Any bugs, please e-mail me@micksam7.com
// [^originally written because of some host limitations :)]
//For https, you will need to have ssl support compiled into your php binary. See php.net to get this setup.
//You can simply put null for all arguements that you don't use, this function does type checking
//Make sure your url is properly encoded, this script cannot tell the difference between a proper url [http://example.tld/script?url=http%3A%2F%2Ffish.tld%2F] or a invalid url [http://example.tld/script?url=http://fish.tld/]
//gzdecode was created by a unknown author on php.net - If you are the author of the function please contact me!

//Changes
//Version v1.0:
// -Based off code for NDCBot Socket [older work of mine]
//Version v1.1 Beta 1:
// -Forces length header
// -Rewrote Fetching and Decoding methods to support length header
// -Rewrote chunk decoder
// -Fixed deflate and gzip support, works fully now.
// -First Public Release!
//Version v1.5 Balpha 1:
// -Now supports SSL [requires SSL to be compiled with your php, win32 should be fine]
// -Responce should be response, and now is
// -Removed Cache-Control header on the request header [useless?]
// -Removed Transfer-Encoding for handling GZip and Inflate, Content-Encoding should only be sent. [?]
// -Fixed missing the last forward slash on url formatter [rewrote formatter too]
// -Added port option [site.com:8080 or whatnot]
// -faster content fetching [Redid fetching loop]
// -MaxBuffer is no longer avalible
// -Reorganized Input
// -Redid output stuff [see example above]
// -Added extra headers input
// -Added extended or plain output option      
// -Added type checking

//HTTP Socket returns an array
//Array Elements: (Typically should be checked in order, will not return all values)
//status   - boolan - true on successful output, false on bot error
//server   - boolan - true for remote server successfully replied, false for remote server did not return expected output
//exists   - boolan - true for existing file on remote server, false on non-existant file
//msg      - array  - array of debug output, used only on error - index 0 is typically the header sent to the server, index 1 is typically what the server has returned (unless socket error)
//headers  - array  - array of headers sent to the socket
//response - array  - array of the server's response [HTTP/1.1 200 OK]
//content  - string - the content of the page
//returnedcookies - array - An array of the cookies returned, includes expiring date, etc.
//senthead - string - Debug output.. the header sent to the server for the request
//sentcontent - string - Debug output.. the content set to the server.. such as file upload.. etc.

global $http_socket_errors, $http_socket_http_codes;

$http_socket_errors = array (
					"notfound"    => "HTTP: Could not find the requested file on the server",
					"timeout"     => "HTTP: Could not complete request in the allowed timelimit",
					"socketerror" => "HTTP: Could not connect to remote server",
					"servererror" => "HTTP: Server could not process your request",
					"dnserror"    => "HTTP: The domain name provided was not found on DNS/WINS",
					"neveroccur"  => "HTTP: A error that should never occur, has occured.",
					"invalidinput"=> "HTTP: Some or all of the input data was invalid",
					"nodata"      => "HTTP: No data was returned",
					"nothandled"  => "HTTP: A unknown error has occured",
					"cutout"      => "HTTP: Server refused request",
);

$http_socket_http_codes = array ( // number => what to do (continue, retry, fail [for 404 only! Use retry or error instead], error, skiphead)
				  "100" => "skiphead", //Skip the section and expect another header -- Not needed in list
				  "101" => "error",
				  "200" => "continue",
				  "201" => "continue",
				  "202" => "error",
				  "203" => "retry",
				  "204" => "fail", //No content, same as 404
				  "205" => "continue",
				  "206" => "error", //We can't send partials! o.o
				  "300" => "error", //Not understood by mick o.o
				  "301" => "continue",
				  "302" => "continue",
				  "303" => "continue",
				  "304" => "error",
				  "305" => "continue",
				  "306" => "retry",
				  "307" => "continue",
				  "400" => "error",
				  "401" => "retry",
				  "402" => "retry",
				  "403" => "fail", //Sometimes this means no directory listing, also known as 404 to ndcbot
				  "404" => "fail",
				  "405" => "error", //Note: Sometimes thrown by IIS when you try to POST on a static page
				  "406" => "error",
				  "407" => "retry",
				  "408" => "retry",
				  "409" => "retry",
				  "410" => "fail", //"Gone" 404 I would think, but a server error it could be also
				  "411" => "error",
				  "412" => "error",
				  "413" => "retry",
				  "414" => "error",
				  "415" => "error",
				  "416" => "error",
				  "417" => "error",
				  "500" => "retry",
				  "501" => "error",
				  "502" => "retry",
				  "503" => "retry",
				  "504" => "error",
				  "505" => "error",
);

//Tools Below

function http_socket_format_cookies ($cookies,$domain=null) { //Formats and/or sorts cookies for bot usage
		 foreach ($cookies as $id => $cookie) {
		 		 $cookies[$id] = $cookie['value'];
		 }
		 return $cookies;
}

function http_socket_cookie_mysql_in ($temp) { //Used in some of my scripts
		 $cookies = array();
		 while ($t = mysql_fetch_array($temp)) {
	  	 	    $tp = $t['name'];
	  	 	 	unset ($t['name']);
	  	 	 	$t['expires'] = gmdate("D, d-M-y H:i:s",$t['expires']) . " GMT";
		 	  	$cookies[$tp] = $t;
		 }
		 return $cookies;
}

//Rest of stuff below

function gzdecode($data) { //GZDecode, definate help..
  $len = strlen($data); 
  if ($len < 18 || strcmp(substr($data,0,2),"\x1f\x8b")) { 
   return null;  // Not GZIP format (See RFC 1952) 
  } 
  $method = ord(substr($data,2,1));  // Compression method 
  $flags  = ord(substr($data,3,1));  // Flags 
  if ($flags & 31 != $flags) { 
   // Reserved bits are set -- NOT ALLOWED by RFC 1952 
   return null; 
  } 
  // NOTE: $mtime may be negative (PHP integer limitations) 
  $mtime = unpack("V", substr($data,4,4)); 
  $mtime = $mtime[1]; 
  $xfl  = substr($data,8,1); 
  $os    = substr($data,8,1); 
  $headerlen = 10; 
  $extralen  = 0; 
  $extra    = ""; 
  if ($flags & 4) { 
   // 2-byte length prefixed EXTRA data in header 
   if ($len - $headerlen - 2 < 8) { 
     return false;    // Invalid format 
   } 
   $extralen = unpack("v",substr($data,8,2)); 
   $extralen = $extralen[1]; 
   if ($len - $headerlen - 2 - $extralen < 8) { 
     return false;    // Invalid format 
   } 
   $extra = substr($data,10,$extralen); 
   $headerlen += 2 + $extralen; 
  } 

  $filenamelen = 0; 
  $filename = ""; 
  if ($flags & 8) { 
   // C-style string file NAME data in header 
   if ($len - $headerlen - 1 < 8) { 
     return false;    // Invalid format 
   } 
   $filenamelen = strpos(substr($data,8+$extralen),chr(0)); 
   if ($filenamelen === false || $len - $headerlen - $filenamelen - 1 < 8) { 
     return false;    // Invalid format 
   } 
   $filename = substr($data,$headerlen,$filenamelen); 
   $headerlen += $filenamelen + 1; 
  } 

  $commentlen = 0; 
  $comment = ""; 
  if ($flags & 16) { 
   // C-style string COMMENT data in header 
   if ($len - $headerlen - 1 < 8) { 
     return false;    // Invalid format 
   } 
   $commentlen = strpos(substr($data,8+$extralen+$filenamelen),chr(0)); 
   if ($commentlen === false || $len - $headerlen - $commentlen - 1 < 8) { 
     return false;    // Invalid header format 
   } 
   $comment = substr($data,$headerlen,$commentlen); 
   $headerlen += $commentlen + 1; 
  } 

  $headercrc = ""; 
  if ($flags & 1) { 
   // 2-bytes (lowest order) of CRC32 on header present 
   if ($len - $headerlen - 2 < 8) { 
     return false;    // Invalid format 
   } 
   $calccrc = crc32(substr($data,0,$headerlen)) & 0xffff; 
   $headercrc = unpack("v", substr($data,$headerlen,2)); 
   $headercrc = $headercrc[1]; 
   if ($headercrc != $calccrc) { 
     return false;    // Bad header CRC 
   } 
   $headerlen += 2; 
  } 

  // GZIP FOOTER - These be negative due to PHP's limitations 
  $datacrc = unpack("V",substr($data,-8,4)); 
  $datacrc = $datacrc[1]; 
  $isize = unpack("V",substr($data,-4)); 
  $isize = $isize[1]; 

  // Perform the decompression: 
  $bodylen = $len-$headerlen-8; 
  if ($bodylen < 1) { 
   // This should never happen - IMPLEMENTATION BUG! 
   return null; 
  } 
  $body = substr($data,$headerlen,$bodylen); 
  $data = ""; 
  if ($bodylen > 0) { 
   switch ($method) { 
     case 8: 
       // Currently the only supported compression method: 
       $data = gzinflate($body); 
       break; 
     default: 
       // Unknown compression method 
       return false; 
   } 
  } else { 
   // I'm not sure if zero-byte body content is allowed. 
   // Allow it for now...  Do nothing... 
  } 

  // Verifiy decompressed size and CRC32: 
  // NOTE: This may fail with large data sizes depending on how 
  //      PHP's integer limitations affect strlen() since $isize 
  //      may be negative for large sizes. 
  if ($isize != strlen($data) || crc32($data) != $datacrc) { 
   // Bad format!  Length or CRC doesn't match! 
   return false; 
  } 
  return $data; 
}

function http_socket_process($msg,$debug,$returndebug) {
  global $http_socket_errors; 
  if ($returndebug) {
    return $debug;
  }
  if ($msg == null) {
    return $debug['content'];
  }
  return $http_socket_errors[$msg] . "\nHTTP: ". $debug['msg'];
}

function http_socket_decode_headers($headers) {
 	    $headers = explode("\n",$headers);
	    $response = explode(" ",$headers[0]);
	    unset($headers[0]);
	    
	    $headerz = $headers;
	    $headers = array();
	    $sentcookie = array();

	    foreach ($headerz as $head) {
	        if ($head == null) continue; //Skip any blank headers, such as the last one
	    	$head = explode(': ',$head);
	    	$id = $head[0];
			unset ($head[0]);
	    	$head = implode(': ',$head);
			if (strtolower($id) == 'set-cookie') {
			   $sentcookie[] = $head;
			}
		    if (isset($headers[$id])) {
		      $t = 1;
		      while (isset($headers[$id."[{$t}]"])) {
		       $t++;
		      }
		      $id .= "[{$t}]";
		     }
			$headers[$id] = $head;
		}
	    
	    $realheaders = $headers;
	    
	    $headers = array(); //Make lowercase for better support
		
		foreach ($realheaders as $id => $head) {
			$headers[strtolower($id)] = strtolower($head);
		}

	    foreach ($sentcookie as $id => $cook) {
	    	$cook = explode('; ',$cook);
			unset($sentcookie[$id]);
			list($id, $value) = explode('=',$cook[0]);
			unset($cook[0]);
			$sentcookie[urldecode($id)] = $cook;
			foreach ($sentcookie[$id] as $pid => $cook) {
	    		$cook = explode('=',$cook);
	    		$tid = $cook[0];
				unset ($cook[0],$sentcookie[$id][$pid]);
	    		$cook = implode('=',$cook);
				$sentcookie[$id][$tid] = $cook;
			}
			$sentcookie[$id]['value'] = urldecode($value);
	    }
	    return array($response,$headers,$sentcookie,$realheaders);
}

function http_socket($url,$post=array(),$refer=null,$cookie=array(),$socktimeout=10,$extraheaders=null,$debug=false)  {
		 //// // "fish/request accepted by processor\n";
		 
		 if (!is_array($post)) $post = array();
		 if (!is_string($refer)) $refer = null;
		 if (!is_array($cookie)) $cookie = array();
		 if (!is_numeric($socktimeout)) $socktimeout = 10;
		 if (!is_string($extraheaders)) $extraheaders = null;
		 if (!is_bool($debug)) $debug = false;
		 
		 //Stuff in case we get a location header.
		 $redirect = array("url"=>$url,"cookie"=>$cookie,"timeout"=>$socktimeout,"extraheaders"=>$extraheaders);
		 
		 //POST or GET
		 if ($post != null && count($post) > 0) {
		 	$method = "POST";
		 	$contenttype = "\nContent-Type: application/x-www-form-urlencoded";
		 } else {
		    $method = "GET";
		    $contenttype = "";
		 }
		 
		 //Set up refer..er..
		 if ($refer != null) {
		 	$refer = "\nReferer: {$refer}";
		 }
		 
		 //Cookie..
		 if ($cookie != null && count($cookie) > 0) {
		 	$cookiedata = array();
		 	foreach ($cookie as $id => $cook) {
		 			$cookie[$id] = urlencode($id) . "=" . urlencode($cook);
		 	}
		 	//$cookie = array("\nCookie: ");
		 	//$t = 0;
		 	//foreach ($cookiedata as $cook) {
		 	//   $cookie[$t] .= $cook . "; ";
		 	//   if (strlen($cookie[$t]) > 1024) { 
			//	  $t++;
			//	  $cookie[$t] = "\nCookie: ";
			//   }
		 	//}
		 	
		 	$cookie = "\nCookie: " . implode("; ",$cookie)." ";
		} else {
			$cookie = null;
		}
		
		//Post
		if ($method == "POST") {
		 	$postdata = array();
		 	foreach ($post as $id => $p) {
		 			$postdata[] = urlencode($id) . "=" . urlencode($p);
		 	}
		 	$content = implode("&",$postdata);
		 	unset($postdata);
		 	$contentlenght = "\nContent-Length: " . strlen($content);
		} else {
		  	$contentlenght = '';
		  	$content = "";
		}

		//Setup url
		$url = explode("/",$url);
		$secure = 0;
		
		if ($url[0] == "https:" || $url[0] == "http:") {
		  $secure = $url[0] == "https:" ? 1 : 0;
		  if (!isset($url[1]) || $url[1] != null || !isset($url[2]) || $url[2] == null) { //Error'd
		    return http_socket_process("invalidinput",array("msg"=>"invalid url input - No other debug avalible - url decoder detected http/https url, but have found slash one set or slash two empty [http:// - http:/-1-/-2-]"),$debug);
		  }
		  unset($url[0],$url[1]);
		}
		
		$url = array_reverse($url); //<Find better method
        $host = array_pop($url);    //<------------------
		$url = array_reverse($url); //<------------------
		
		$nexthost = $host; //Save in case of redirect
		
		$path = "/" . implode("/",$url);
		array_pop($url); //Take off file
		$nextpath = $url;
		
		unset($url);
		
		/*
split by /
if [0] is https or http, set secure/unsecure, add https/http to nexthost || else add http
if above is true, check to see if [1] is set. If so, error invalid input
if 2 is not set, and if 1 is true, then output invalid input
if all else is good, unset [0] and [1]
set host to the next found peice in array | set as host | add to nexthost
create nextpath from nexthost
take off first peice in array
implode rest of array into the path
take off last peice of array
take rest of the array, add to nextpath
*/

		//END URL

				
		//Set up header and prep the request.
		$header = <<< EOF
{$method} {$path} HTTP/1.1
Accept: image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, application/x-shockwave-flash, */*
Accept-Language: en-us
Connection: close
Host: {$host}{$refer}{$cookie}
User-Agent: Capped.TV Feeder Cache Socket
Accept-Encoding: deflate{$contenttype}{$contentlenght}
EOF;

// Fix php ide formatting issues... */

   	    //Clean up
   	    //unset($refer,$contentlenght,$contenttype,$method,$path);
   	    //Reset after BETA
   	    
   	    // "Data Ready\nConnecting\n";
   	  
            $server = $host;

        //Connect
        if (!$secure) {
          $port = isset($port) ? $port : 80;
	   	  $con = @fsockopen($server, $port, $errno, $errstr, $socktimeout);
	    } else {
	      $port = isset($port) ? $port : 443;
	   	  $con = @fsockopen("ssl://".$server, $port, $errno, $errstr, $socktimeout);
	    }
	    
	    if (!$con) {
   	       unset($host);
   	       return http_socket_process("socketerror",array('msg'=>"Could not connect, SockErr: [{$errno}] {$errstr}"),$debug);
   	    }
   	    
	    // "Connected\n";
   	    
   	    //Set Timeout
   	    stream_set_timeout($con, $socktimeout);
	    stream_set_blocking($con, 1);
   	    
   	    // "Socket Options Set\n";

   	    fwrite($con, $header); //Send header to the server.
   	    // "-DATA-\n" . $header . "\n-DATA END-\n";
   	    // "Sent Header\n";
   	    fwrite($con, "\r\n\r\n"); //End header message
   	    // "Sent Header End\n";
		
   	    if (isset($content) && $content != null) { //If we have anything else to send..
		   fwrite($con, $content); //Send it now!
   	    }
   	    // "Sent any other data\n";
   	    
   	    //Fetch Headers
   	    
   	    $headers = "";
   	    // "Waiting for header\n";
   	    while (!isset($response) || $response == null) { //While the the responce is not set or equals 100
   	        $in = "";
   	        while (strpos($in,"\n") === FALSE && !feof($con)) {
   	            $recv = fgets($con);
			    $in .= str_replace("\r","",$recv);
				// "-B-" . $recv . "-E-"; 
			    // "\nGot a header\n";
			}
			
			if (feof($con)) {
			  return http_socket_process("cutout",array("msg"=>"The server exited before request could be confirmed."),$debug);
			}
		    
		    if (trim($in) == null) {
			   list($response,$headers,$sentcookie,$realheaders) = http_socket_decode_headers($headers);
			   if ($response[1] == "100") { //Are We Done?
			     unset($headers,$sentcookie,$realheaders);
			   }
                     if ($response[1] == "404") {
                       return "404"; //Hack for dlookup.php
                     }
			} else {
			  $headers .= $in; //Down here so we don't include the last newline
			}
		}
		
		// "Header receaved\n";
		
		//If we are redirected
		
		if ((isset($headers['location']) || isset($headers['redirect'])) && !$debug) {
		  // "Redirect detected. Forcing break.\n";
		  
		  ////////////////////////////////////////////////////////////////////////////
		  //die("HTTP: redirect\n"); //Below is not finnished.. Use debug directive to get around this atm
		  ////////////////////////////////////////////////////////////////////////////
		  
		  fclose($con);
		  $to = isset($headers['location']) ? $headers['location'] : $headers['redirect'];
		  return array("redirect"=>$to);
		  
		  
		  		  
		  // "$to\n";
		  return http_socket($to,null,$redirect['url'],array_merge(http_socket_format_cookies($sentcookie),$redirect['cookie']),$socktimeout,$extraheaders);
		}
		
		//Fetch Contents
		
		$return = array();
		$fetched = 0;
		
		// "Fetching contents\n";
	    
		while (!feof($con)) {
		  
			if (isset($headers['transfer-encoding']) && strtolower($headers['transfer-encoding']) == "chunked") { //Find next fetch length & Tell when done
			  $fetchlen = fgets($con);
			  // "DEBUG: hex {$fetchlen}\n";
			  if ($fetchlen == "" || str_replace("\r","",$fetchlen) == "\n") continue;
			  $fetchlen = hexdec($fetchlen);
			  $fetched = 0;
			  // "DEBUG: Chunk: Fetching {$fetchlen}\n";
			  if ($fetchlen == 0) {
			    // "DEBUG: End Chunk Receaved\n";
			    break;
			  }
			} else if (isset($headers['content-length'])) {
			  if (!isset($fetchlen)) {
			    $fetchlen = $headers['content-length'];
			    // "Receaving {$fetchlen}, by header\n";
			  } else {
			    break;
			  }
			} else {
			  // "No length receaved. Receaving till eof or timeout\n";
			  $fetchlen = 65565; //Max!
			}
			
			while ($fetched < $fetchlen && !feof($con)) {
			  $fetch = (($fetchlen - $fetched) > 65565) ? 65565 : $fetchlen-$fetched;
			  // "DEBUG: Getting {$fetch} of {$fetchlen}\n";
			  $in = fread($con,$fetch);
			  //// "DEBUG: {$in}\n";
			  $return[] = $in;
			  $fetched += strlen($in);
			  // "DEBUG: Got {$fetched}\n";
			}
			
   	    }
   	    
   	    $return = implode("",$return);
	    sleep(1);
	    $extra = fread($con,1024);
	    // "Found ".strlen($extra)." extra bytes..\n";
   	    
   	    // "Receaved " . strlen($return) . " bytes total\n";
		
   	    fclose($con); //Close our server's connection, we are done.	
		
		// "Socket closed. Checking responce headers\n";
		
		global $http_socket_http_codes;

	    switch ($http_socket_http_codes[$response[1]]) {
		case 'fail':
			return http_socket_process("notfound",array('status'=>true,'server'=>true,'exists'=>false,'headers'=>$realheaders,'response'=>$response,'content'=>$return,'senthead'=>$header,'sentcontent'=>$content),$debug);
		case 'retry':
			return http_socket_process("servererror",array('status'=>true,'server'=>false,'exists'=>false,'headers'=>$realheaders,'response'=>$response,'content'=>$return,'senthead'=>$header,'sentcontent'=>$content),$debug);
		case 'error':
			return http_socket_process("nothandled",array('status'=>false,'server'=>false,'exists'=>false,'headers'=>$realheaders,'response'=>$response,'content'=>$return,'senthead'=>$header,'sentcontent'=>$content),$debug);
		case 'continue':
			break;
		default:
			return http_socket_process("neveroccur",array('status'=>true,'server'=>false,'exists'=>false,'headers'=>$realheaders,'response'=>$response,'content'=>$return,'senthead'=>$header,'sentcontent'=>$content),$debug);
	    }
	    
	    // "Responce was not error, checking content encoding\n";
		
		if (isset($headers['content-encoding'])) {
	      switch ($headers['content-encoding']) {
		  case 'gzip':
		    file_put_contents("out.gz",$return);
		// "Decoding with gzip decode\n";
		    $return = gzdecode($return);
		    break;
 		  case 'deflate':
			// "Decoding with inflate\n";
			$return = gzinflate($return);
			break;
	      } 
		}

	    if (get_magic_quotes_runtime() == 1) {
		   $return = stripslashes($return);
		   // "Magic Quotes on, stripping slashes\n";
	  	}
		
		// "Done. Returning results to caller.\n";
		
   	    return http_socket_process("",array('status'=>true,'server'=>true,'exists'=>true,'headers'=>$realheaders,'response'=>$response,'content'=>$return,'returnedcookies'=>$sentcookie,'senthead'=>$header,'sentcontent'=>$content),$debug);
}

?>