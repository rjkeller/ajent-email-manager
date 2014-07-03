/*
WordGrab Lightbox Script
Based on code by Lokesh Dhakar - http://www.huddletogether.com
*/

function getPageScroll(){ var yScroll; if (self.pageYOffset) { yScroll = self.pageYOffset;} else if (document.documentElement && document.documentElement.scrollTop){ yScroll = document.documentElement.scrollTop;} else if (document.body) { yScroll = document.body.scrollTop;}
arrayPageScroll = new Array('',yScroll)
return arrayPageScroll;}
function getPageSize(){ var xScroll, yScroll; if (window.innerHeight && window.scrollMaxY) { xScroll = document.body.scrollWidth; yScroll = window.innerHeight + window.scrollMaxY;} else if (document.body.scrollHeight > document.body.offsetHeight){ xScroll = document.body.scrollWidth; yScroll = document.body.scrollHeight;} else { xScroll = document.body.offsetWidth; yScroll = document.body.scrollHeight + document.body.offsetHeight;}
var windowWidth, windowHeight; if (self.innerHeight) { windowWidth = self.innerWidth; windowHeight = self.innerHeight;} else if (document.documentElement && document.documentElement.clientHeight) { windowWidth = document.documentElement.clientWidth; windowHeight = document.documentElement.clientHeight;} else if (document.body) { windowWidth = document.body.clientWidth; windowHeight = document.body.clientHeight;}
if(yScroll < windowHeight){ pageHeight = windowHeight;} else { pageHeight = yScroll;}
if(xScroll < windowWidth){ pageWidth = windowWidth;} else { pageWidth = xScroll;}
arrayPageSize = new Array(pageWidth,pageHeight,windowWidth,windowHeight)
return arrayPageSize;}
function pause(numberMillis) { var now = new Date(); var exitTime = now.getTime() + numberMillis; while (true) { now = new Date(); if (now.getTime() > exitTime)
return;}
}
function getKey(e){ if (e == null) { keycode = event.keyCode;} else { keycode = e.which;}
key = String.fromCharCode(keycode).toLowerCase(); 
}
function listenKey () { document.onkeypress = getKey;}
function showLightbox(objLink)
{
	var objOverlay = document.getElementById('overlay');
	var objLightbox = document.getElementById('lightbox');
	var objCaption = document.getElementById('lightboxCaption');
	var objImage = document.getElementById('lightboxImage');
	var objLoadingImage = document.getElementById('loadingImage');
	var objLightboxDetails = document.getElementById('lightboxDetails');
	var arrayPageSize = getPageSize();
	var arrayPageScroll = getPageScroll();

	objOverlay.style.height = (arrayPageSize[1] + 'px');
	objOverlay.style.display = 'block';

	var lightboxTop = arrayPageScroll[1] + ((arrayPageSize[3] - 35) / 4);
	var lightboxLeft = ((arrayPageSize[0] - 20) / 3);
	objLightbox.style.top = (lightboxTop < 0) ? "0px" : lightboxTop + "px";
	objLightbox.style.left = (lightboxLeft < 0) ? "0px" : lightboxLeft + "px";
	objCaption.style.display = 'block';
	objCaption.innerHTML = objLink.innerHTML;
	if (navigator.appVersion.indexOf("MSIE")!=-1){
		pause(250);
	}

	objLightbox.style.display = 'block';
	arrayPageSize = getPageSize();
	objOverlay.style.height = (arrayPageSize[1] + 'px');
	listenKey();
}
function hideLightbox()
{ objOverlay = document.getElementById('overlay'); objLightbox = document.getElementById('lightbox'); objOverlay.style.display = 'none'; objLightbox.style.display = 'none'; selects = document.getElementsByTagName("select"); for (i = 0; i != selects.length; i++) { selects[i].style.visibility = "visible";}
document.onkeypress = '';}
function initLightbox()
{ if (!document.getElementsByTagName){ return;}
var anchors = document.getElementsByTagName("a"); for (var i=0; i<anchors.length; i++){ var anchor = anchors[i]; if (anchor.getAttribute("href") && (anchor.getAttribute("rel") == "lightbox")){ anchor.onclick = function () {showLightbox(this); return false;}
}
}
var objBody = document.getElementsByTagName("body").item(0); var objOverlay = document.createElement("div"); objOverlay.setAttribute('id','overlay'); objOverlay.onclick = function () {hideLightbox(); return false;}
objOverlay.style.display = 'none'; objOverlay.style.position = 'absolute'; objOverlay.style.top = '0'; objOverlay.style.left = '0'; objOverlay.style.zIndex = '9000000'; objOverlay.style.width = '100%'; objBody.insertBefore(objOverlay, objBody.firstChild); var arrayPageSize = getPageSize(); var arrayPageScroll = getPageScroll();
var objLightbox = document.createElement("div"); objLightbox.setAttribute('id','lightbox'); objLightbox.style.display = 'none'; objLightbox.style.position = 'absolute'; objLightbox.style.zIndex = '10000000'; objBody.insertBefore(objLightbox, objOverlay.nextSibling);
var imgPreloadCloseButton = new Image(); imgPreloadCloseButton.onload=function(){ var objCloseButton = document.createElement("img"); objCloseButton.src = "/images/lightbox-close.gif"; objCloseButton.setAttribute('id','closeButton'); objCloseButton.style.position = 'absolute'; objCloseButton.style.zIndex = '200'; return false;}
imgPreloadCloseButton.src = "/images/lightbox-close.gif"; var objLightboxDetails = document.createElement("div"); objLightboxDetails.setAttribute('id','lightboxDetails'); objLightbox.appendChild(objLightboxDetails); var objCaption = document.createElement("div"); objCaption.setAttribute('id','lightboxCaption'); objCaption.style.display = 'none'; objLightboxDetails.appendChild(objCaption); var objKeyboardMsg = document.createElement("div"); objKeyboardMsg.setAttribute('id','keyboardMsg'); objLightboxDetails.appendChild(objKeyboardMsg);}
function addLoadEvent(func)
{ var oldonload = window.onload; if (typeof window.onload != 'function'){ window.onload = func;} else { window.onload = function(){ oldonload(); func();}
}
}
addLoadEvent(initLightbox);



// Copyright 2006-2007 javascript-array.com

var timeout	= 500;
var closetimer	= 0;
var ddmenuitem	= 0;
var oldEditButton = null;

// open hidden layer
function mopen(id, editBoxId)
{	
	// cancel close timer
	mcancelclosetime();

	// close old layer
	if(ddmenuitem) ddmenuitem.style.visibility = 'hidden';

	// get new layer and show it
	ddmenuitem = document.getElementById(id);
	ddmenuitem.style.visibility = 'visible';

	if (editBoxId != undefined)
	{
		var editBox = document.getElementById(editBoxId);
		editBox.className = "editButton editButtonHover";

		if (oldEditButton != null)
		{
			var editBox = document.getElementById(oldEditButton);
			editBox.className = "editButton";
		}

		oldEditButton = editBoxId;
	}
}
// close showed layer
function mclose(editBoxId)
{
	if(ddmenuitem) ddmenuitem.style.visibility = 'hidden';

	if (editBoxId != undefined)
	{
		var editBox = document.getElementById(editBoxId);
		editBox.className = "editButton";
	}

}

// go close timer
function mclosetime(editBoxId)
{
	closetimer = window.setTimeout('mclose("' + editBoxId + '")', timeout);
}

// cancel close timer
function mcancelclosetime(editBoxId)
{
	if(closetimer)
	{
		window.clearTimeout(closetimer);
		closetimer = null;
	}
}

// close layer when click-out
document.onclick = mclose;
