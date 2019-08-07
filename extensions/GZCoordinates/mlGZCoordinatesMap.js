/*
	MYSTlore Great Zero Coordinates Special Page for MediaWiki
	http://code.google.com/p/mystlore/

	Created 2007-04-18 by Soeren Kuklau

	mlGZCoordinatesSpecialPage.js: global JavaScript additions for geographical unit conversions

	Copyright 2006-07 MYSTlore contributors. Some rights reserved.

	Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

	The full license can be retrieved at:
	http://www.opensource.org/licenses/mit-license.php
*/

// kudos to http://www.quirksmode.org/js/findpos.html
function findPos(obj) {
	var curleft = curtop = 0;
	if (obj.offsetParent) {
		curleft = obj.offsetLeft
		curtop = obj.offsetTop
		while (obj = obj.offsetParent) {
			curleft += obj.offsetLeft
			curtop += obj.offsetTop
		}
	}
	return [curleft,curtop];
}

// compare whether point 1 is within <dither> pixels of point 2
function mlGZComparePoints(x1, y1, x2, y2, dither) {
	if ((Math.abs(x1-x2) + Math.abs(y1-y2)) > dither) {
		return false;
	} else {
		return true;
	}
}

function mlGZRemoveMapPointLabel(event) {
	var elem = event.target;

	elem.parentNode.removeChild(elem);
}

function mlGZShowMapPointLabel(event, width, height) {
	var divElements = document.getElementsByTagName("div");
	var i, len = divElements.length;

	for (i = 0; i < len; i++) {
		var elem = divElements[i];
		if (elem.className == "gzMapPointLabel") {
//			elem.parentNode.removeChild(elem);
		}
	}

	posArray = findPos(document.getElementById('mlGZMap'));
	mapLeft = posArray[0]+10;
	mapTop = posArray[1]+10;

	widthFactor = document.getElementById('mlGZMap').width / width;
	heightFactor = document.getElementById('mlGZMap').height / height;

	var labelElement = document.createElement('div');

	labelElement.className = "gzMapPointLabel";

	labelElement.style.left = event.pageX+"px";
	labelElement.style.top = event.pageY+"px";
	labelElement.style.position = "absolute";

	labelElement.style.zIndex = "3";

	labelElement.addEventListener('mouseout', mlGZRemoveMapPointLabel, false);

	for (point in mlGZCoordinates) {
		if (mlGZComparePoints(event.pageX, event.pageY, (mlGZCoordinates[point][1]*widthFactor)+mapLeft, (mlGZCoordinates[point][2]*heightFactor)+mapTop, 5)) {
			labelElement.innerHTML = mlGZCoordinates[point][0];
		}
	}

	document.getElementById('column-content').appendChild(labelElement);
}

