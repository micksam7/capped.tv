<?php
//404 hack
//by the mickelphin

//Get the end of the url
$urlstub = substr($_SERVER['QUERY_STRING'], strrpos($_SERVER['QUERY_STRING'],":80/")+4);
$urlstub = substr($urlstub, 0, (strpos($urlstub,"?")===false?strlen($urlstub):strpos($urlstub,"?")));
$params = explode(",",$urlstub);

foreach ($params as $id => $param) {
  $_GET['arg'.$id] = $param;
}

//Fix broken IIS parsing
foreach ($_GET as $id => $data) {
  $_GET[substr($id, strpos($id,"?")===false?0:strpos($id,"?")+1)] = $data;
}

//Then after that we load up the index and everything runs the same.

include("index.php");

?>
