<?php
require "includes/funky.php";
require "../capped.private.php";
require "includes/sceneid.php";

error_reporting(E_ALL);

session_start();
session_destroy();
session_start();

if ($_POST['user'] == null || $_POST['pass'] == null) exit("Form wasn't filled out. :(");

try {
	$data = SceneId::Factory(SCENEID_LOGIN, SCENEID_PASSWORD)->login($_POST['user'],$_POST['pass'])->asSimpleXML();
} catch (Exception $e) {
	var_dump($e);
	echo "SceneID seems to be down. Try again later.";
	exit();
}

if ($data->returnCode == 30) {
	if ($data->user->verified != 1) {
		exit("Your account is not verified. Please contact scene.org staff about that.");
	}
	$_SESSION['loggedIn'] = true;
	$_SESSION['sceneid_uid'] = (int) $data->user->id;
	session_write_close();
	header("Location: /upload.php");
} else {
	if (!isset($data->returnMessage) || $data->returnMessage == null) {
		echo "SceneID sent invalid data. Contact Mick or try again later.";
		//echo "<!-- ".var_export($data,true)." -->";
	} else {
		echo "SceneID Says: ".$data->returnMessage;
	}
}

?>