<?php
//Capped.TV's Soap
//By the Magic Dolphin [micksam7.com]

require "api-functions.php";
require "NuSOAP/nusoap.php";

$server = new soap_server;
$server->configureWSDL('CappedTVapi', 'urn:CappedTVapi', 'http://capped.tv/api-soap.php');

/*****************************/
/******* GENERAL TYPES *******/
/*****************************/

//linkVar - Contains a single link
$server->wsdl->addComplexType(
    'linkVar',
    'complexType',
    'struct',
    'all',
    '',
    array(
        'name' => array('name' => 'name', 'type' => 'xsd:string'),
        'url'  => array('name' => 'url',  'type' => 'xsd:string'),   
    )
);

//linksList - contains links!
$server->wsdl->addComplexType(
    'linksList',
    'complexType',
    'array',
    'all',
    'SOAP-ENC:Array',
    array(),
    array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:linkVar[]')),
    'tns:linksList'
);

//vidVar - contains all details about a video
$server->wsdl->addComplexType(
    'vidVar',
    'complexType',
    'struct',
    'all',
    '',
    array(
        'id'         => array('name' => 'id',         'type' => 'xsd:string'),
        'name'       => array('name' => 'name',       'type' => 'xsd:string'),
        'author'     => array('name' => 'author',     'type' => 'xsd:string'),
    	'width'      => array('name' => 'width',      'type' => 'xsd:int'),
    	'height'     => array('name' => 'height',     'type' => 'xsd:int'),
    	'bitrate'    => array('name' => 'bitrate',    'type' => 'xsd:int'),
    	'postTime'   => array('name' => 'postTime',   'type' => 'xsd:int'),
    	'views'      => array('name' => 'views',      'type' => 'xsd:int'),
    	'monthViews' => array('name' => 'monthViews', 'type' => 'xsd:int'),
    	'uploader'   => array('name' => 'uploader',   'type' => 'xsd:string'),
    	'uploaderId' => array('name' => 'uploaderId', 'type' => 'xsd:int'),
    	'comment'    => array('name' => 'comment',    'type' => 'xsd:string'),
    	'links'      => array('name' => 'links',      'type' => 'tns:linksList'),
    	'videoLink'  => array('name' => 'videoLink',  'type' => 'xsd:string'),
	'flv6Link'   => array('name' => 'flv6Link',   'type' => 'xsd:string'),
    	'imageLink'  => array('name' => 'imageLink',  'type' => 'xsd:string'),
    	'audioLink'  => array('name' => 'audioLink',  'type' => 'xsd:string'),
    	'nfoLink'    => array('name' => 'nfoLink',    'type' => 'xsd:string')
    )
);

$server->wsdl->addComplexType(
    'vidList',
    'complexType',
    'array',
    'all',
    'SOAP-ENC:Array',
    array(),
    array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:vidVar[]')),
    'tns:vidList'
);

/*****************************/
/********* Services **********/
/*****************************/

$server->register('getVidById',                // method name
    array('videoId' => 'xsd:string'),        // input parameters
    array('return' => 'tns:vidVar'),      // output parameters
    'urn:CappedTVapi',                      // namespace
    'urn:CappedTVapi#getVidById',                // soapaction
    'rpc',                                // style
    'encoded',                            // use
    'Gets all avalible video info by video id [vId]. Note: this returns a blank struct if none found.'            // documentation
);

$server->register('findVidsByPouetId',                // method name
    array('pouetId' => 'xsd:int'),        // input parameters
    array('return' => 'tns:vidList'),      // output parameters
    'urn:CappedTVapi',                      // namespace
    'urn:CappedTVapi#findVidsByPouetId',                // soapaction
    'rpc',                                // style
    'encoded',                            // use
    'Gets all videos with the spec. pouet id. Note: Returns a empty array/struct if none found.'            // documentation
);

$server->register('findVidsByUserId',                // method name
    array('userId' => 'xsd:int'),        // input parameters
    array('return' => 'tns:vidList'),      // output parameters
    'urn:CappedTVapi',                      // namespace
    'urn:CappedTVapi#findVidsByUserId',                // soapaction
    'rpc',                                // style
    'encoded',                            // use
    'Gets all videos uploaded by the spec. sceneid user id. Note: Returns a empty array/struct if none found.'            // documentation
);

$server->register('getAllVids',                // method name
    array(),        // input parameters
    array('return' => 'tns:vidList'),      // output parameters
    'urn:CappedTVapi',                      // namespace
    'urn:CappedTVapi#getAllVids',                // soapaction
    'rpc',                                // style
    'encoded',                            // use
    'Gets all publicly viewable videos. Period.'            // documentation
);

$server->register('getLatestVids',                // method name
    array('howMany' => 'xsd:int'),        // input parameters
    array('return' => 'tns:vidList'),      // output parameters
    'urn:CappedTVapi',                      // namespace
    'urn:CappedTVapi#getLatestVids',                // soapaction
    'rpc',                                // style
    'encoded',                            // use
    'Gets x amount of videos last posted.'            // documentation
);

$server->register('getTopVids',                // method name
    array('howMany' => 'xsd:int'),        // input parameters
    array('return' => 'tns:vidList'),      // output parameters
    'urn:CappedTVapi',                      // namespace
    'urn:CappedTVapi#getTopVids',                // soapaction
    'rpc',                                // style
    'encoded',                            // use
    'Gets x amount of videos that are most popular.'            // documentation
);

$server->register('getBottomVids',                // method name
    array('howMany' => 'xsd:int'),        // input parameters
    array('return' => 'tns:vidList'),      // output parameters
    'urn:CappedTVapi',                      // namespace
    'urn:CappedTVapi#getBottomVids',                // soapaction
    'rpc',                                // style
    'encoded',                            // use
    'Gets x amount of videos that are least popular.'            // documentation
);

$server->register('getTopMonthVids',                // method name
    array('howMany' => 'xsd:int'),        // input parameters
    array('return' => 'tns:vidList'),      // output parameters
    'urn:CappedTVapi',                      // namespace
    'urn:CappedTVapi#getTopMonthVids',                // soapaction
    'rpc',                                // style
    'encoded',                            // use
    'Gets x amount of videos that are most popular for this month.'            // documentation
);

$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);
?>