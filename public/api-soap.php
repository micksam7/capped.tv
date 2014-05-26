<?php

require "api-functions.php";
$soap = new SoapServer("http://capped.tv/api.php?wsdl");
$soap->addFunction(SOAP_FUNCTIONS_ALL);
$soap->handle();

?>