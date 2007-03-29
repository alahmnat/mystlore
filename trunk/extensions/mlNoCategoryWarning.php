<?php

/*
	MYSTlore No Category Warning for MediaWiki
	http://code.google.com/p/mystlore/

	Created 2007-03-29 by Soeren Kuklau

	Copyright 2007 MYSTlore contributors. Some rights reserved.

	Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

	The full license can be retrieved at:
	http://www.opensource.org/licenses/mit-license.php
*/

$wgHooks['ParserBeforeInternalParse'][] = "mlNoCategoryWarning"; // BeforePageDisplay not supported by MediaWiki 1.6.x

function mlNoCategoryWarning(&$parser, &$text) {
	$title =& $parser->getTitle();

	// only act on main (article) namespace
	// TODO: should be extended to act on any article namespace
	// must *not* act on 'Category:' namespace; will cause havoc!
	if ($title->getNamespace() != NS_MAIN) {
		return true;
	}

	preg_match('/\[\[Category:.*\]\]/', $text, &$matches);

	// TODO: doesn't actually add article to category (possible at all?)
	if (count($matches) == 0) {
		$text .= "\n".'<div class="mlRed-block"><strong>Note:</strong> This page has no categories. [[Editor]]s, please try to add suitable [[Special:Categories|existing]] or new categories to it.'."\n[[Category:Uncategorized Pages]]\n\n";
	}

	return true;
}
?>
