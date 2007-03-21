/*
	MYSTlore D'ni Tag for MediaWiki
	http://code.google.com/p/mystlore/

	Created 2006-11-05 by Soeren Kuklau

	mlDniTag.js: global JavaScript additions to warn if font not found

	Copyright 2006-07 MYSTlore contributors. Some rights reserved.

	Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

	The full license can be retrieved at:
	http://www.opensource.org/licenses/mit-license.php
*/

/*
	Portions from:

	JavaScript/CSS Font Detector
	http://www.lalit.org/lab/fontdetect.php

	Copyright 2007 lalit.org

	Licensed under the Apache License, Version 2.0 (the "License");
	you may not use this file except in compliance with the License.
	You may obtain a copy of the License at

		http://www.apache.org/licenses/LICENSE-2.0

	Unless required by applicable law or agreed to in writing, software
	distributed under the License is distributed on an "AS IS" BASIS,
	WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
	See the License for the specific language governing permissions and
	limitations under the License.
*/

/*
	Portions from:

	stchur talks <javascript>: "Retrieving Elements by Class Name"
	http://ecmascript.stchur.com/2006/11/29/retrieving_elements_by_class_name

	Copyright 2006
*/

var Detector = function() {
	var h = document.getElementsByTagName("BODY")[0];
	var d = document.createElement("DIV");
	var s = document.createElement("SPAN");

	d.appendChild(s);
	d.style.fontFamily = "sans-serif";		//font for the parent element DIV.
	s.style.fontFamily = "sans-serif";		//arial font used as a comparator.
	s.style.fontSize   = "72px";			//we test using 72px font size, we may use any size. I guess larger the better.
	s.innerHTML        = "mmmmmmmmmml";		//we use m or w because these two characters take up the maximum width. And we use a L so that the same matching fonts can get separated
	h.appendChild(d);
	var defaultWidth   = s.offsetWidth;		//now we have the defaultWidth
	var defaultHeight  = s.offsetHeight;	//and the defaultHeight, we compare other fonts with these.
	h.removeChild(d);

	/* test
	 * params:
	 * font - name of the font you wish to detect
	 * return: 
	 * f[0] - Input font name.
	 * f[1] - Computed width.
	 * f[2] - Computed height.
	 * f[3] - Detected? (true/false).
	 */
	function test(font) {
		h.appendChild(d);
		var f = [];
		f[0] = s.style.fontFamily = font;	// Name of the font
		f[1] = s.offsetWidth;				// Width
		f[2] = s.offsetHeight;				// Height
		h.removeChild(d);

		font = font.toLowerCase();
		if (font == "arial" || font == "sans-serif") {
			f[3] = true;	// to set arial and sans-serif true
		} else {
			f[3] = (f[1] != defaultWidth || f[2] != defaultHeight);	// Detected?
		}
		return f;
	}
	this.test = test;
}

function checkDnifont() {
	d = new Detector();

	if (d.test("Dnifont")[3]) {
		var supElements = document.getElementsByTagName("sup");
		var i, len = supElements.length;

		for (i = 0; i < len; i++) {
			var elem = supElements[i];
			if (elem.className == "mlDnifontNote") {
				elem.style.display = 'none';
			}
		}
	} else {
		document.getElementById("mlDnifontWarning").style.display = 'block';
	}
}
