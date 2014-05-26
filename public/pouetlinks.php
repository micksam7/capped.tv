<pre>
<?php
require "funky.php";

$db = getdatabase();

$vids = mysql_query("SELECT pouetid, vidid, title FROM ac_vids WHERE pouetid != 0 AND pouetid IS NOT NULL ORDER BY date DESC LIMIT 10",$db) or exit(mysql_error());

while ($vid = mysql_fetch_array($vids)) {
	echo "[url=http://capped.tv/{$vid['vidid']}]Capped.TV[/url] for [url=http://pouet.net/prod.php?which={$vid['pouetid']}]{$vid['title']}[/url]\n\n";
}

?>