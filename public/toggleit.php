<?php

if ($_GET['what'] == "noVideoThumbs") {
	if (isset($_COOKIE['noVideoThumbs']) && $_COOKIE['noVideoThumbs'] == 1) {
		setcookie("noVideoThumbs",0,time()+(60*60*24*365));
	} else {
		setcookie("noVideoThumbs",1,time()+(60*60*24*365));
	}
}

if ($_GET['what'] == "noHtml5Video") {
	if (isset($_COOKIE['noHtml5Video']) && $_COOKIE['noHtml5Video'] == 1) {
		setcookie("noHtml5Video",0,time()+(60*60*24*365));
	} else {
		setcookie("noHtml5Video",1,time()+(60*60*24*365));
	}
}

if (!isset($_SERVER['HTTP_REFERER']) || $_SERVER['HTTP_REFERER'] == null) {
	exit("I donno where you came from, so please go back yourself.");
}

header("Location: ".$_SERVER['HTTP_REFERER'])

?>