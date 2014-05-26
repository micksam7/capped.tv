<?php 
//Note you must have SOAP enabled in PHP for this to work 
//Otherwise, you can try NuSOAP [google it] 
//NuSOAP uses http://capped.tv/api.php instead of ?wsdl 
// but works mostly the same way as PHP's default 

echo "<pre>"; //For some formatting for those who just want to run this in a browser 

//Create SOAP instance 
$capped = new SoapClient('http://capped.tv/api.php?wsdl',array('trace'=>true)); 

//Get last 15 new videos 
try {
$latestVideos = $capped->getLatestVids(3); 
} catch (Exception $e) {}
var_dump($latestVideos); 

//Get video by it's vid 
$lifeforce = $capped->getVidById("asd-lifeforce"); 
var_dump($lifeforce); 

//Grab every video for the fun of it 
//$allVideos = $capped->getAllVids(); 
//var_dump($allVideos); //Spammy! 

?>