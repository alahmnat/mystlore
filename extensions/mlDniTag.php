<?php

/*
	MYSTlore D'ni Tag for MediaWiki
	http://code.google.com/p/mystlore/

	Created 2006-11-05 by Soeren Kuklau

	Copyright 2006-07 MYSTlore contributors. Some rights reserved.

	Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

	The full license can be retrieved at:
	http://www.opensource.org/licenses/mit-license.php
*/

$wgExtensionFunctions[] = "mlDniTag";
$wgHooks['OutputPageBeforeHTML'][] = "mlDniTagScripting"; // BeforePageDisplay not supported by MediaWiki 1.6.x

function mlDniTagScripting(&$out, &$text) {
	$extensionsDirectory = "$wgScriptPath/extensions";

	// with BeforePageDisplay, we could properly place the script tag in the head instead of the body
	$text = "\n		".'<script type="text/javascript" src="'.$extensionsDirectory.'/mlDniTag.js"></script>'."\n".'<div id="mlDnifontWarning" class="mlRed-block" style="display: none;"><strong>Note:</strong> This page contains text written in <a href="/wiki/D%27ni_script">'."D'ni</a>. To be view it properly, you need to have the D'ni script font installed on your computer, and it appears you don't. ".'<a href="/wiki/MYSTlore:D%27ni_text_input"><em>Click here for more information.</em></a></div>'."\n\n".$text."\n\n".'<script type="text/javascript">checkDnifont();</script>';
}

function mlDniTag() {
	global $wgParser;

	$wgParser->setHook("d'ni",	"renderDniScript");
	$wgParser->setHook("D'ni",	"renderDniScript"); // are these tags even case-sensitive at all?
}

function renderDniScript($input, $argv, &$parser) {
	return "<span style=\"font: 14pt Dnifont; padding: 0 5px 0 5px;\">$input</span><sup class=\"mlDnifontNote\">&#91;<a href=\"http://www.mystlore.com/wiki/MYSTlore:D%27ni_text_input\">?</a>&#93;</sup>";
}
?>
