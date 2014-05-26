<?php
/**
 * Capped.TV Uploader
 * 
 * @author micksam7
 */

$title = "Juice Some Fruit";
include "includes/header.php";
require "api-functions.php";

try {
	$data = getLogin();
	$loggedin = true;
} catch (Exception $e) {
	$loggedin = false;
	unset($data);
}

if (isset($data) && $data->login == "koreanclan") { exit("Capped.TV is only for demoscene-related videos. Please upload elsewhere."); }

?>
<body id="deadbody">
<center>
<img src="img/upload.png" alt="Capped Header" />
<table width="700" class="main">
<tr>
<td rowspan=12 class="M">
<b>How To Juice your Fruit:</b><br /><br />
Capped uses a pseudo-patented Juicing method to turn nearly all videos into portable Juice Containers [Caps].
These containers can be played by any machine with the latest <a href="http://www.adobe.com/go/getflashplayer">Adobe Flash Player</a> installed.
<br /><br />
- Capped assumes you've already captured the Fruit you'd like to be squeezed.<br />
- Simply package your fruit in a compatable video codec, nearly all are supported, and use the form to properly label and send your Fruit to our Squeezing Machine.<br />
- After a few minutes [10 to 20], depending on how busy the machine is, your fruit will be Capped and be ready for public consumption.
<br /><br />
<b>Note:</b><br />Capped is designed for only a specific type of Fruit.<br />
Please, only upload <b>demoscene</b> related materials. Our Capped Container cannot handle
non-demoscene videos and such materials uploaded to Capped may spontaneously disappear soon after Juicing.
If you do not know what the demoscene is, then you shouldn't be uploading here.<br /><br />
<b>Video Codecs and Quality Concerns:</b><br />
Capped supports nearly all video and audio codecs for uploading. Any codec <a href="http://ffmpeg.mplayerhq.hu/">ffmpeg</a> can read, Capped can accept.<br />
When uploading to capped, please be sure your video is of utmost quality. Any noticeable encoding artifact will definately be duplicated onto Capped. Capped's quality is nearly equal to the video input.
<br /><br />

</td>
<?php
if ($loggedin) {
	$user = $data->nickname == null || $data->nickname == $data->login ? "<b>{$data->login}</b>" : "<b>{$data->login}</b>, aka <b>{$data->nickname}</b>";
	$user .= " [SceneID: {$data->id}]";
?>'
<td width="450" class="M">You are logged in as <?=$user ?>. <a href="logout.php">Logout</a></td></tr>
<?php } else { ?>
<td width="450" class="M">To upload a file, you must sign in using your <a href="http://scene.org/">SceneID</a>. You probably have one if you signed up on <a href="http://pouet.net/">Pouet</a>.</td></tr>
<tr><td class="M"><b>Login:</b> <form action="login.php" method="post"><input type="text" name="user" /> <input type="password" name="pass" /> <input type="submit" value="Try it" />
</form></td></tr>
<?php }
if ($loggedin) { ?>
<form enctype="multipart/form-data" action="submit.php" method="post"><input type="hidden" name="MAX_FILE_SIZE" value="1073741824" />
<tr><td class="M"><b>Title:</b> <input type="text" name="atitle" /></td></tr>
<tr><td class="M"><b>By:</b> <input type="text" name="by" /> [seperate multiple with a &amp; please!]</td></tr>
<tr><td class="M"><b>Links:</b><br /><textarea style="width: 430px" rows=5 name="info">
POUET: 
</textarea><br />
Only POUET link is needed. Will auto-generate all other relevant links for you!<br />
You can have up to 5 links. Format: "DISPLAY NAME: http://url/" + newline - Remove the lines that aren't used.
</td></tr>
<tr><td class="M"><b>Comments:</b> <input type="text" name="comment" /> [comments about video capture]</td></tr>
<tr><td class="M"><b>Video:</b><br /><input type="file" name="video" /> [local file, all common video formats allowed]<br />
<b>-or-</b><br />
<input type="text" name="videourl" /> [url - http and ftp supported]</td></tr>
<tr><td class="M"><b>MP4 Upload</b><br />
<input type="checkbox" name="mp4" value="true" /> Check this if video file is a MP4 already and is in h.264 &amp; AAC+<br />
This will bypass the encoder entirely.<br />
Please ensure the video will play on flash.<br />
If you're not sure, try one of the many FLV players out there, such as <a href="http://www.martijndevisser.com/blog/flv-player/">this one</a>. <br />
Videos must be under a sane size [1080p is fine] and under 7,500 kbit average bitrate.<br />
If you'd like to upload something bigger, <a href="mailto:capped@micksam7.com">Ask Us</a>.<br />
But if you don't wanna fiddle with any of that, leave this option unchecked.
</td></tr>
<?php } ?>
<tr><td class="M"><b>Upload Policy:</b><br />
Quick reminder that this service is for <b>demoscene</b> videos only.<br />
Any mismarked or non-demoscene video will be removed.<br />
Repeatedly uploading invalid content can get you banned.<br />
Please do not upload content that is illegal in the USA<br />
</td></tr>
<?php if ($loggedin) { ?>
<tr><td class="M">If you understand and agree to the terms above, you may submit! <input type="submit" value="Cap it!" /></td></tr>
<?php } ?>
<tr><td height="100%"> </td></tr>
</table>
<?php if ($loggedin) { ?></form><?php } ?>
<img src="img/uploadb.png" />
</center>
<?php require "includes/footer.php"; ?>