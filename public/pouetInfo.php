<?php
function pouetInfo($id) {
require_once "cachesocket.php";

$id = (int) $id;

$page = httpCacheSocket("http://pouet.net/prod.php?which={$id}");

//Seperate it down to the top table
$page = substr($page, strpos($page, '<table bgcolor="#000000" cellspacing="1" cellpadding="0" border="0" width="100%">'));
$page = substr($page, 0, strpos($page, '<table bgcolor="#000000" cellspacing="1" cellpadding="0" border="0" width="100%">', 2));

//Remove preview image
$page1 = substr($page, 0, strpos($page, '<td rowspan="3" align="center" valign="center" nowrap>'));
$page2 = substr($page, strpos($page, '<td rowspan="3" align="center" valign="center" nowrap>'));
$page = $page1 . substr($page2, strpos($page2, '</td>')+5);

//Fix stuff
$page = str_replace("nowrap","",$page);

//Fix image urls
$page = str_replace("<img src=\"","<img src=\"http://pouet.net/",$page);

//Break out urls
$pageUrls = explode("<a href=\"",$page);
$page = array_shift($pageUrls); //Top of page

foreach ($pageUrls as $id => $urlSeg) {
	$page .= "<a href=\"";
	if (strpos($urlSeg, "http:") !== 0) {
		$page .= "http://pouet.net/" . $urlSeg;
	} else {
		$page .= $urlSeg;
	}
}

//Add css
$page = str_replace(array("<td","<tr","<a"),array("<td class=\"pouet\"","<tr class=\"pouet\"","<a class=\"pouet\""),$page);


return $page;
}
?>