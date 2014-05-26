//this scrap by micksam7

//Because I like include()ing stuff, I donno why <3
function include(file) {
	document.write('<script type="text/javascript" src="' + file + '"></script>'); 
}

include ("img/NLB_bgFade.js");


//For form box thinger
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
	document.embed.code.value = '<object width="'+document.embed.widthh.value+'" height="'+new_height+'"><param name="movie" value="http://capped.micksam7.com/playeralt.swf?vid='+ac_vid+quality+'" /><param name="wmode" value="direct" /><param name="allowFullScreen" value="true" /><param name="bgcolor" value="#000000" /><embed src="http://capped.micksam7.com/playeralt.swf?vid='+ac_vid+quality+'" type="application/x-shockwave-flash" wmode="direct" bgcolor="#000000" allowFullScreen="true" width="'+document.embed.widthh.value+'" height="'+new_height+'"></embed></object>';
	return false;
}


//BG Fader with love
function fadeBg() {
	var blackorwhite = getCookie("ac_bgcolor");
	if (blackorwhite == "1") { //To White!
		NLBfadeBg("deadbody","000000","FFFFFF",1000);
		NLBfadeBg("spanna","FFFFFF","000000",1000);
		setCookie("ac_bgcolor",0,360);
	} else { 
		NLBfadeBg("deadbody","FFFFFF","000000",1000);
		NLBfadeBg("spanna","000000","FFFFFF",1000);
		setCookie("ac_bgcolor",1,360);
	}
}


//Ripped from w3c
function setCookie(c_name,value,expiredays) {
	var exdate=new Date();
	exdate.setDate(exdate.getDate()+expiredays);
	document.cookie=c_name+ "=" +escape(value)+((expiredays==null) ? "" : ";expires="+exdate.toGMTString());
}

function getCookie(c_name) {
	if (document.cookie.length>0) {
		c_start=document.cookie.indexOf(c_name + "=");
		if (c_start!=-1) {
			c_start=c_start + c_name.length+1; 
			c_end=document.cookie.indexOf(";",c_start);
			if (c_end==-1) c_end=document.cookie.length;
			return unescape(document.cookie.substring(c_start,c_end));
		} 
	}
	return "";
}