<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<style>
* {
	font-size: 12px;
	text-decoration: none;
}

a {
	color: #0050FF;
}

body {
	margin: 0px;
	background: #FFFFFF;
	font-family: Tahoma, Ariel;
	color: #000000;
}

i {
	font-size: 8px;
}

div.main {
	text-align: left;
	width: 500px;
}

div.code {
	border: 1px black solid;
	margin: 5px;
	padding: 5px;
	background-color: #DFDFFF;
	}
</style>
<title>Capped - Authorized Juice Distributor Procedures</title>
</head>
<body>
<center><img src="api-capped.png" alt="Capped API logo" /><br />
<div class="main">
Capped now offers ways to become a Authorized Juice Distributor. Once you've become a Distributor [and you are already],
you can use our Delivery Services [aka API] to get infomation on avalible Juices [aka videos] for Delivery through our
Capped Bottle Player, or your own Bottle Player [aka flash video player].<br /><br />
<img src="soap-api.png" align="left" alt="Soap API" />The most powerful of these services is our SOAP API.
<a href="http://en.wikipedia.org/wiki/SOAP">SOAP</a> is a object protocol, it is quite easy to impliment in
dynamic languages like PHP.<br /><br />
SOAP methods: <a href="http://capped.tv/api.php">http://capped.tv/api.php</a><br />
WSDL File: <a href="http://capped.tv/api.php?wsdl">http://capped.tv/api-soap.php?wsdl</a>
<br /><br />
<b>Code Example:</b><br />
<div class="code">
<?php
$example = <<< DOC
<?php
//Note you must have SOAP enabled in PHP for this to work [PHP5+]
//  See http://us3.php.net/manual/en/ref.soap.php for info
//For PHP4, or where SOAP isn't accessable, you can use
//  NuSOAP or Pear:SOAP

echo "<pre>"; //For some formatting for those who just want to run this in a browser

//Create SOAP instance
\$capped = new SoapClient('http://capped.tv/api-soap.php?wsdl');

//Get last 15 new videos
\$latestVideos = \$capped->getLatestVids(15);
var_dump(\$latestVideos);

//Get video by it's vid
\$lifeforce = \$capped->getVidById("asd-lifeforce");
var_dump(\$lifeforce);

//Grab every video for the fun of it
\$allVideos = \$capped->getAllVids();
var_dump(\$allVideos); //Spammy!

?>
DOC;

highlight_string($example);

?>
</div>
<br /><br />
<img src="xml-api.png" alt="XML API" align="left" />For those who cannot/do not want to use the SOAP api, may use our
XML api instead. It has all the same methods as the SOAP api.<br /><br />
XML api URL: <a href="http://capped.tv/api-xml.php">http://capped.tv/api-xml.php</a>
<br /><br /><br /><br /><br />
</div>
</center>
</body>
</html>
 