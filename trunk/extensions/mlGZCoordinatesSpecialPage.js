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

var toranteeDegreesConversionFactor = 62500/360;
var shahfeeteeFeetConversionFactor = 40/3; // approximation
var shahfeeteeMetersConversionFactor = 4.064; // approximation
var feetMetersConversionFactor = 0.3048;

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
	var spanElements = document.getElementsByTagName("span");
	var i, len = spanElements.length;

	switch (document.getElementById('angleUnit').selectedIndex) {
		case 0: // degrees to torantee
			document.getElementById('angle').value *= toranteeDegreesConversionFactor;

			for (i = 0; i < len; i++) {
				var elem = spanElements[i];
				if (elem.className == "angleValue") {
					elem.innerHTML *= toranteeDegreesConversionFactor;
				}
			}

			break;

		case 1: // torantee to degrees
			document.getElementById('angle').value /= toranteeDegreesConversionFactor;

			for (i = 0; i < len; i++) {
				var elem = spanElements[i];
				if (elem.className == "angleValue") {
					elem.innerHTML /= toranteeDegreesConversionFactor;
				}
			}

			break;
	}

	roundValues(); // re-round values after changing them
}

var originalDistanceUnit = document.getElementById('distanceUnit').selectedIndex;

function changeDistanceUnit() {
	var spanElements = document.getElementsByTagName("span");
	var i, len = spanElements.length;

	var newDistanceUnit = document.getElementById('distanceUnit').selectedIndex;

	distanceUnitConcat = originalDistanceUnit.toString() + newDistanceUnit.toString();

	switch (distanceUnitConcat) {
		case "01": // shahfeetee to feet
			document.getElementById('distance').value *= shahfeeteeFeetConversionFactor;

			for (i = 0; i < len; i++) {
				var elem = spanElements[i];
				if (elem.className == "distanceValue") {
					elem.innerHTML *= shahfeeteeFeetConversionFactor;
				}
			}

			break;

		case "02": // shahfeetee to meters
			document.getElementById('distance').value *= shahfeeteeMetersConversionFactor;

			for (i = 0; i < len; i++) {
				var elem = spanElements[i];
				if (elem.className == "distanceValue") {
					elem.innerHTML *= shahfeeteeMetersConversionFactor;
				}
			}

			break;

		case "10": // feet to shahfeetee
			document.getElementById('distance').value /= shahfeeteeFeetConversionFactor;

			for (i = 0; i < len; i++) {
				var elem = spanElements[i];
				if (elem.className == "distanceValue") {
					elem.innerHTML /= shahfeeteeFeetConversionFactor;
				}
			}

			break;

		case "12": // feet to meters
			document.getElementById('distance').value *= feetMetersConversionFactor;

			for (i = 0; i < len; i++) {
				var elem = spanElements[i];
				if (elem.className == "distanceValue") {
					elem.innerHTML *= feetMetersConversionFactor;
				}
			}

			break;

		case "20": // meters to shahfeetee
			document.getElementById('distance').value /= shahfeeteeMetersConversionFactor;

			for (i = 0; i < len; i++) {
				var elem = spanElements[i];
				if (elem.className == "distanceValue") {
					elem.innerHTML /= shahfeeteeMetersConversionFactor;
				}
			}

			break;

		case "21": // meters to feet
			document.getElementById('distance').value /= feetMetersConversionFactor;

			for (i = 0; i < len; i++) {
				var elem = spanElements[i];
				if (elem.className == "distanceValue") {
					elem.innerHTML /= feetMetersConversionFactor;
				}
			}

			break;
	}

	originalDistanceUnit = newDistanceUnit;

	roundValues(); // re-round values after changing them
}

var originalElevationUnit = document.getElementById('elevationUnit').selectedIndex;

function changeElevationUnit() {
	var spanElements = document.getElementsByTagName("span");
	var i, len = spanElements.length;

	var newElevationUnit = document.getElementById('elevationUnit').selectedIndex;

	elevationUnitConcat = originalElevationUnit.toString() + newElevationUnit.toString();

	switch (elevationUnitConcat) {
		case "01": // shahfeetee to feet
			document.getElementById('elevation').value *= shahfeeteeFeetConversionFactor;

			for (i = 0; i < len; i++) {
				var elem = spanElements[i];
				if (elem.className == "elevationValue") {
					elem.innerHTML *= shahfeeteeFeetConversionFactor;
				}
			}

			break;

		case "02": // shahfeetee to meters
			document.getElementById('elevation').value *= shahfeeteeMetersConversionFactor;

			for (i = 0; i < len; i++) {
				var elem = spanElements[i];
				if (elem.className == "elevationValue") {
					elem.innerHTML *= shahfeeteeMetersConversionFactor;
				}
			}

			break;

		case "10": // feet to shahfeetee
			document.getElementById('elevation').value /= shahfeeteeFeetConversionFactor;

			for (i = 0; i < len; i++) {
				var elem = spanElements[i];
				if (elem.className == "elevationValue") {
					elem.innerHTML /= shahfeeteeFeetConversionFactor;
				}
			}

			break;

		case "12": // feet to meters
			document.getElementById('elevation').value *= feetMetersConversionFactor;

			for (i = 0; i < len; i++) {
				var elem = spanElements[i];
				if (elem.className == "elevationValue") {
					elem.innerHTML *= feetMetersConversionFactor;
				}
			}

			break;

		case "20": // meters to shahfeetee
			document.getElementById('elevation').value /= shahfeeteeMetersConversionFactor;

			for (i = 0; i < len; i++) {
				var elem = spanElements[i];
				if (elem.className == "elevationValue") {
					elem.innerHTML /= shahfeeteeMetersConversionFactor;
				}
			}

			break;

		case "21": // meters to feet
			document.getElementById('elevation').value /= feetMetersConversionFactor;

			for (i = 0; i < len; i++) {
				var elem = spanElements[i];
				if (elem.className == "elevationValue") {
					elem.innerHTML /= feetMetersConversionFactor;
				}
			}

			break;
	}

	originalElevationUnit = newElevationUnit;

	roundValues(); // re-round values after changing them
}
