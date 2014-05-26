/* Layout, design, and code is Copyright 2010 micksam7 - me@micksam7.com */

function updateEmbed() {
	var new_width = document.embed.widthh.value / ac_width;
	var new_height = Math.round(ac_height * new_width) + 20;
	if (document.embed.widthh.value <= 640) {
		var quality = "|mq";
	} else if (document.embed.widthh.value <= 450) {
		var quality = "|lq";
	} else {
		var quality = "";
	}
	document.embed.code.value = '<object width="'+document.embed.widthh.value+'" height="'+new_height+'"><param name="movie" value="http://capped.micksam7.com/play.swf?vid='+ac_vid+quality+'" /><param name="wmode" value="direct" /><param name="allowFullScreen" value="true" /><param name="bgcolor" value="#000000" /><embed src="http://capped.micksam7.com/play.swf?vid='+ac_vid+quality+'" type="application/x-shockwave-flash" wmode="direct" bgcolor="#000000" allowFullScreen="true" width="'+document.embed.widthh.value+'" height="'+new_height+'"></embed></object>';
	return false;
}

var sentStatistics = false;

/**
 * Bug fixing for sublime video lib
 */ 
function checkSublime() {
	//Fix flash wmode for better performence
	if (document.getElementsByName("sublimevideo-flash1")[0] != null && document.getElementsByName("sublimevideo-flash1")[0].getAttribute("wmode") == "transparent") {
		//document.getElementsByName("sublimevideo-flash1")[0].style.display = "none"; //Forces object to take new wmode param
		//document.getElementsByName("sublimevideo-flash1")[0].setAttribute("wmode","direct");
		//setTimeout("document.getElementsByName(\"sublimevideo-flash1\")[0].style.display = \"inherit\";",1000);
		//Oh, this also means we're playing now.
		sendStatistics();
	}

	if (document.getElementById("videoBox") != null && document.getElementById("videoBox").currentTime != 0) {
		sendStatistics();
	}
}

setInterval("checkSublime();",100);

/**
 * Send playback log statistic stuff
 */
function sendStatistics() {
	if (!sentStatistics) {
		sentStatistics = true;
		var xmlSock = getXMLSock();
		xmlSock.open("POST","/capped-statistics.php",true);
		xmlSock.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		xmlSock.send("id="+ac_vid);
	 }
}

/**
 * Gets a XML Socket
 */
function getXMLSock() {
	var xmlSock;
	try {
		xmlSock = new XMLHttpRequest(); //Your smarter web browsers
	} catch (e) {
		try {
			xmlSock = new ActiveXObject("Microsoft.XMLHTTP"); //Your dumber web browsers
		} catch (e) {
			return true; //Your really "smart" web browsers
		}
	}
	return xmlSock;
}