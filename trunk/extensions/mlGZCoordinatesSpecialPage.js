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

function roundValues() {
	var spanElements = document.getElementsByTagName("span");
	var i, len = spanElements.length;

	for (i = 0; i < len; i++) {
		var elem = spanElements[i];
		if ((elem.className == "angleValue") ||
		(elem.className == "distanceValue") ||
		(elem.className == "elevationValue") ||
		(elem.className == "distanceFromValue")) {
			elem.innerHTML = Math.round(elem.innerHTML*100)/100;
		}
	}
}

function changeAngleUnit() {
	angleUnitTDConversionFactor = 62500/360; // 62500 torantee ^= 360 degrees

	var spanElements = document.getElementsByTagName("span");
	var i, len = spanElements.length;

	switch (document.getElementById('angleUnit').selectedIndex) {
		case 0: // degrees to torantee
			document.getElementById('angle').value *= angleUnitTDConversionFactor;

			for (i = 0; i < len; i++) {
				var elem = spanElements[i];
				if (elem.className == "angleValue") {
					elem.innerHTML *= angleUnitTDConversionFactor;
				}
			}

			break;

		case 1: // torantee to degrees
			document.getElementById('angle').value /= angleUnitTDConversionFactor;

			for (i = 0; i < len; i++) {
				var elem = spanElements[i];
				if (elem.className == "angleValue") {
					elem.innerHTML /= angleUnitTDConversionFactor;
				}
			}

			break;
	}

	roundValues(); // re-round values after changing them
}

// function changeDistanceUnit() {
// 	angleUnitSFConversionFactor = 1/1.1234; // 1 span ^= 1.1234 feet
// 	angleUnitSMConversionFactor = 1/1.23; // 1 span ^= 1.23 meters
// 	angleUnitFMConversionFactor = 1/0.3048; // 1 foot ^= 0.3048 meters
// 
// 	var spanElements = document.getElementsByTagName("span");
// 	var i, len = spanElements.length;
// 
// 	switch (document.getElementById('distanceUnit').selectedIndex) {
// 		case 0: // degrees to torantee
// 			document.getElementById('distance').value *= distanceUnitConversionFactor;
// 
// 			for (i = 0; i < len; i++) {
// 				var elem = spanElements[i];
// 				if (elem.className == "angleValue") {
// 					elem.innerHTML *= angleUnitConversionFactor;
// 				}
// 			}
// 
// 			break;
// 
// 		case 1: // torantee to degrees
// 			document.getElementById('angle').value /= angleUnitConversionFactor;
// 
// 			for (i = 0; i < len; i++) {
// 				var elem = spanElements[i];
// 				if (elem.className == "angleValue") {
// 					elem.innerHTML /= angleUnitConversionFactor;
// 				}
// 			}
// 
// 			break;
// 	}
// }
