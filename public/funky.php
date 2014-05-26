<?php

//funky functions

function getdatabase() { 
  exit("STOP - Old DB Call");
}

function getXml($page) {
  $hash = md5($page);
  //if (file_exists("xml-cache/{$md5}.serial") && ) 
}

?>