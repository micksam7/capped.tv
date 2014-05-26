<pre><?php
include("sceneid.php");
include("../.private/capped.private.php");

$data = SceneId::Factory(SCENEID_LOGIN, SCENEID_PASSWORD)->getUserInfoById(17342)->asSimpleXML();

var_dump((array)$data);
?>