/* Layout, design, and code is Copyright 2010 micksam7 - me@micksam7.com */

var frames = [];
var mousePosX = 0;
var mousePosY = 0 ;

function frameAdd(object) {
	frames[frames.length] = {"object": object};
}

function frameInterval() {
	var debug = document.getElementById("debug");
	
	for (var t=0; t<frames.length; t++) {
		//Check video playback, for firefox/other browsers that don't loop.
		if (document.getElementById("thumb"+t).ended) {
			document.getElementById("thumb"+t).play();	
		}
	}
}