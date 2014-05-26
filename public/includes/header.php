<?php
/**
 * Your generic header file
 * 
 * @author micksam7
 */


header("Content-Type: text/html; charset=utf-8", true);

/*if (isset($_COOKIE['ac_bgcolor']) && $_COOKIE['ac_bgcolor'] == 1) { //Black
	$c1 = "FFFFFF";
	$c2 = "000000";
} else {*/
	$c2 = "FFFFFF";
	$c1 = "000000";
//}

if (!isset($title)) {
	$title = "Turning Fruit into Juice";
}

if (!isset($headers)) {
	$headers = "";
}

echo '<?xml version="1.0" encoding="UTF-8"?>';

?>
<!DOCTYPE html 
     PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<link rel="stylesheet" href="img/style.css" type="text/css" media="all" title="CTV" />
<style type="text/css">
body {
	background: #<?=$c2 ?>;
	color: #<?=$c1 ?>;
}
</style>
<script type="text/javascript" src="img/js.js"></script>
<title>Capped.TV - <?=$title ?></title>
<?=$headers ?>
</head>
