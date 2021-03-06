<?php

/*
	MYSTlore Great Zero Coordinates Special Page for MediaWiki
	http://code.google.com/p/mystlore/

	Created 2007-04-16 by Soeren Kuklau

	Copyright 2006-07 MYSTlore contributors. Some rights reserved.

	Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

	The full license can be retrieved at:
	http://www.opensource.org/licenses/mit-license.php
*/

$wgExtensionFunctions[] = "mlGZCoordinatesSpecialPageLoader";
//$wgHooks['LangugeGetSpecialPageAliases'][] = "mlGZLocalizedSpecialPageName";
$wgHooks['ArticleSaveComplete'][] = "mlGZAddCoordinate";

require_once( "$IP/includes/SpecialPage.php" );

function mlGZCoordinatesSpecialPageLoader() {
	global $IP, $wgMessageCache, $wgParser;

	$wgParser->setHook("mlGZCoordinateList", "renderGZCoordinateList");
	$wgParser->setHook("GZrelativeDistance", "renderGZrelativeDistance");
	SpecialPage::addPage(new GZCoordinatesSpecialPage());
}

function renderGZCoordinateList($input, $argv, &$parser) {
	$parser->setOutputType(OT_WIKI);

	$output .= '<p><small>(<i>Editors:</i> Please, do not edit this page. Rather, if you wish to add or correct a location, place a gz-coords template (or correct the existing one) inside the appropriate article for the location whose coordinates you wish to add. E.g., to store the coordinates of the Tokotah Courtyard, edit <a href="/wiki/Tokotah_Courtyard" title="Tokotah Courtyard">Tokotah Courtyard</a>. Whilst an article may have more than one such template, this list always corresponds to the very first one.)</small>
	</p><p>Below is a list of <b><a href="/wiki/Great_Zero" title="Great Zero">Great Zero</a></b> coordinates that have been collected. You can use the <a href="/wiki/Special:GZCoordinatesSpecialPage" title="Special:GZCoordinatesSpecialPage">Great Zero Coordinates special page</a> to compare coordinates with each other, and to have their relative distances "as the crow flies" calculated.</p>

<ul>
';

	$contentArray = preg_split('/[\n\r]+/', $input);

	foreach ($contentArray as $key=>$value) {
		// filter out invalid lines
		if (preg_match_all('/\|/', $value, $matches) != 3) {
			unset($contentArray[$key]);
			continue;
		}

		// ensure only one line per article
		$line = explode('|', $value);

		$output .= '	<li><strong><a href="/wiki/'.$line[0].'">'.$line[0]."</a>:</strong> ".$line[1].", ".$line[2].", ".$line[3]."</li>\n";
	}

	$output .= "</ul>";

	return $output;
}

function renderGZrelativeDistance($input, $argv, &$parser) {
	$output = '<script type="text/javascript" src="'.$wgScriptPath.'/extensions/mlGZCoordinatesSpecialPage.js"></script>';

	$title =& $parser->mTitle;

	$coord1 =& GreatZeroCoordinate::newFromTitle($input);
	$coord2 =& GreatZeroCoordinate::newFromTitle($title->getPrefixedText());

	if (! is_object($coord1)) {
		return '';
	}

	$output .= '<span class="distanceFromValue">'.$coord1->distance_from($coord2).'</span>';

	// FIXME: there's got to be a more efficient way to round *once*
	$output .= '<script type="text/javascript">roundValues();</script>';

	return $output;
}

function mlGZLocalizedSpecialPageName(&$specialPageArray) {
	static $messagesLoaded = false;
	global $wgMessageCache;
	if ($messagesLoaded) return;
	$messagesLoaded = true;

	require(dirname(__FILE__) . '/mlGZCoordinatesSpecialPage.i18n.php' );

	foreach ($mlGZCoordinatesMessages as $lang => $langMessages) {
		$wgMessageCache->addMessages($langMessages, $lang);
	}

	$specialPageArray['GZCoordinatesSpecialPage'][] = wfMsg('gzcoordinatesspecialpage');

	return true;
}

function mlGZAddCoordinate(&$editedArticle) {
	$editedTitle =& $editedArticle->getTitle();
	if ($editedTitle->getNamespace() != NS_MAIN) {
		return true;
	}

	if (preg_match('/{{gz-coord.*}}/', $editedArticle->getContent(), $templateMatches)) { // only update if the article has such a template
		$article =& getArticleFollowingRedirects(wfMsg('mlGZCoordinateListPage'));
		$content =& $article->getContent();

		$contentArray = preg_split('/[\n\r]+/', $content);

		$before = "<mlGZCoordinateList>\n";
		$newContent = '';
		$after = "\n</mlGZCoordinateList>";
		
		foreach ($contentArray as $key=>$value) {
			// filter out invalid lines
			if (preg_match_all('/\|/', $value, $matches) != 3) {
				unset($contentArray[$key]);
				continue;
			}

			// ensure only one line per article
			$line = explode('|', $value);

			if (strcmp($line[0], $editedTitle->getText()) == 0) {
				unset($contentArray[$key]);
			}

			$newContent .= "\n".trim($contentArray[$key]);
		}

		$coords = explode('|', $templateMatches[0]);
		$newContent .= "\n".trim($editedTitle->getText().'|'.intval($coords[1]).'|'.intval($coords[2]).'|'.intval($coords[3]));

		$article->doEdit($before.trim($newContent).$after, '[mlGZCoordinates automated addition of Great Zero coordinate]', EDIT_UPDATE|EDIT_FORCE_BOT);
	}

	return true;
}

function getArticleFollowingRedirects($inTitle) {
	$title = Title::newFromText($inTitle);
	$title->invalidateCache();

	$article = new Article($title);
	$content =& $article->getContent();

	# FIXME: this is from Title.php#newFromRedirect. There should be a better way of 'following a redirect only if needed'.
	$mwRedir = MagicWord::get('redirect');
	$rt = NULL;
	if ($mwRedir->matchStart($content)) {
		$m = array();
		if (preg_match('/\[{2}(.*?)(?:\||\]{2})/', $content, $m)) {
			if (substr($m[1],0,1) == ':') {
				$m[1] = substr( $m[1], 1 );
			}

			$rt = Title::newFromText($m[1]);

			if ( !is_null($rt) && $rt->isSpecial( 'Userlogout' ) ) {
				$rt = NULL;
			}
		}
	}
	if ($rt != NULL) {
		$title = $rt;
		$title->invalidateCache();

		$article = new Article($title);
	}

	return $article;
}

class GZCoordinatesSpecialPage extends SpecialPage {
	function GZCoordinatesSpecialPage() {
		self::loadMessages();

		SpecialPage::SpecialPage('GZCoordinatesSpecialPage');
	}

	function execute( $par ) {
		global $wgUser, $wgOut, $wgTitle, $wgRequest;

		$this->setHeaders();

		$this->angle = $wgRequest->getText('angle');
		$this->distance = $wgRequest->getText('distance');
		$this->elevation = $wgRequest->getText('elevation');

		$this->debug = $wgRequest->getText('debug');

		$this->angleUnit = $wgRequest->getText('angleUnit');
		$this->distanceUnit = $wgRequest->getText('distanceUnit');
		$this->elevationUnit = $wgRequest->getText('elevationUnit');

		$wgOut->addHtml( $this->makeForm() );

		if ((strlen($this->angle) > 0) && (strlen($this->distance) > 0) && (strlen($this->elevation) > 0)) {
			$this->findNearbyLocations();
		}

		if ($this->debug == "yes") {
			$this->showMap();
		}
	}

	function loadMessages() {
		static $messagesLoaded = false;
		global $wgMessageCache;
		if ($messagesLoaded) return;
		$messagesLoaded = true;

		require(dirname(__FILE__) . '/mlGZCoordinatesSpecialPage.i18n.php' );

		foreach ($mlGZCoordinatesMessages as $lang => $langMessages) {
			$wgMessageCache->addMessages($langMessages, $lang);
		}
	}

	private function getHumanUnit($unit, $value) {
		global $wgOut;

		switch ($unit) {
			case 'toran':
				if ($value != 1) {
					$result = 'tee';
				}
				$result = wfMsg('mlGZ'.$unit.$result);

				break;

			case 'degree':
				if ($value != 1) {
					$result = 's';
				}
				$result = wfMsg('mlGZ'.$unit.$result);

				break;

			case 'shahfee':
				if ($value != 1) {
					$result = 'tee';
				}
				$result = wfMsg('mlGZ'.$unit.$result);

				break;

			case 'foot':
				if ($value != 1) {
					$result = wfMsg('mlGZ'.'feet');
				}
				$result = wfMsg('mlGZ'.$unit);

				break;

			case 'meter':
				if ($value != 1) {
						$result = 's';
				}
				$result = wfMsg('mlGZ'.$unit.$result);

				break;
		}

		return $wgOut->parse($result, false);
	}

	// should use wfMsg and wfMsgHtml to localize this stuff
	private function makeForm() {
		global $wgScript, $wgOut;

		$title = self::getTitleFor('GZCoordinatesSpecialPage');
		$form  = '<fieldset><legend>' . wfMsg('mlGZSearchForNearbyLocations') . '</legend>';
		$form .= Xml::openElement( 'form', array( 'method' => 'get', 'action' => $wgScript ) );
		$form .= Xml::hidden( 'title', $title->getPrefixedText() );
		$form .= '<p>';

		$form .= $wgOut->parse('[[Image:KI_angle_icon.png]]', false);
		$form .= '&nbsp;' . Xml::inputLabel(wfMsg('mlGZAngle'), 'angle', 'angle', 10, $this->angle) . '&nbsp;';
		$form .= Xml::openElement('select', array('name' => "angleUnit", 'id' => "angleUnit", 'onchange' => "changeAngleUnit();"));
		if (strcmp($this->angleUnit, "degrees") == 0) {
			$form .= Xml::option($this->getHumanUnit("toran", 0));
			$form .= Xml::option($this->getHumanUnit("degree", 0), '', true);
		} else { // torantee
			$form .= Xml::option($this->getHumanUnit("toran", 0), '', true);
			$form .= Xml::option($this->getHumanUnit("degree", 0));
		}
		$form .= Xml::closeElement('select') . '<span style="padding-right: 2em;">&nbsp;</span>';

		$form .= $wgOut->parse('[[Image:KI_distance_icon.png]]', false);
		$form .= '&nbsp;' . Xml::inputLabel(wfMsg('mlGZDistance'), 'distance', 'distance', 10, $this->distance) . '&nbsp;';
		$form .= Xml::openElement('select', array('name' => "distanceUnit", 'id' => "distanceUnit", 'onchange' => "changeDistanceUnit();"));
		switch ($this->distanceUnit) {
			case 'feet':
				$form .= Xml::option($this->getHumanUnit("shahfee", 0));
				$form .= Xml::option($this->getHumanUnit("foot", 0), '', true);
				$form .= Xml::option($this->getHumanUnit("meter", 0));
				break;

			case 'meters':
				$form .= Xml::option($this->getHumanUnit("shahfee", 0));
				$form .= Xml::option($this->getHumanUnit("foot", 0));
				$form .= Xml::option($this->getHumanUnit("meter", 0), '', true);
				break;

			default: // shahfeetee
				$form .= Xml::option($this->getHumanUnit("shahfee", 0), '', true);
				$form .= Xml::option($this->getHumanUnit("foot", 0));
				$form .= Xml::option($this->getHumanUnit("meter", 0));
				break;
		}
		$form .= Xml::closeElement('select') . '<span style="padding-right: 2em;">&nbsp;</span>';

		$form .= $wgOut->parse('[[Image:KI_elevation_icon.png]]', false);
		$form .= '&nbsp;' . Xml::inputLabel(wfMsg('mlGZElevation'), 'elevation', 'elevation', 10, $this->elevation) . '&nbsp;';
		$form .= Xml::openElement('select', array('name' => "elevationUnit", 'id' => "elevationUnit", 'onchange' => "changeElevationUnit();"));
		switch ($this->elevationUnit) {
			case 'feet':
				$form .= Xml::option($this->getHumanUnit("shahfee", 0));
				$form .= Xml::option($this->getHumanUnit("foot", 0), '', true);
				$form .= Xml::option($this->getHumanUnit("meter", 0));
				break;

			case 'meters':
				$form .= Xml::option($this->getHumanUnit("shahfee", 0));
				$form .= Xml::option($this->getHumanUnit("foot", 0));
				$form .= Xml::option($this->getHumanUnit("meter", 0), '', true);
				break;

			default: // shahfeetee
				$form .= Xml::option($this->getHumanUnit("shahfee", 0), '', true);
				$form .= Xml::option($this->getHumanUnit("foot", 0));
				$form .= Xml::option($this->getHumanUnit("meter", 0));
				break;
		}
		$form .= Xml::closeElement('select') . '<span style="padding-right: 2em;">&nbsp;</span>';

		$form .= Xml::submitButton(wfMsg('mlGZGoButton')) . '</p>';
		$form .= Xml::closeElement( 'form' );
		$form .= '</fieldset>';

		$form .= "\n".'<script type="text/javascript" src="'.$wgScriptPath.'/extensions/mlGZCoordinatesSpecialPage.js"></script>'."\n";

		return $form;
	}

	private function compareLocation($inputCoord, $coords) {
		global $wgOut;

		if (strcmp($this->angleUnit, "degrees") == 0) {
			$useDegrees = true;
		}

		$sortedCoords = array();

		foreach ($coords as $key=>$value) {
			if ($inputCoord->isEquivalentTo($value)) {
				unset($coords[$key]);
				continue;
			}

			$sortedCoords[strval($inputCoord->distance_from($value))]=$value;
		}

		ksort($sortedCoords);

		foreach($sortedCoords as $key=>$value) {
			$parsedLocation = $wgOut->parse("[[".$value->location."]]", false);
			$parsedCoordinate = $wgOut->parse("{{gz-coord|".$value->angle.'|'.$value->distance.'|'.$value->elevation."}}", false);

			$tableResultString .= '<tr><td>'.$parsedLocation.'</td><td>'.$parsedCoordinate.'</td><td><span class="distanceFromValue">'.$key.'</td></tr>'."\n";
		}

		if (strcmp($tableResultString, '') != 0) {
					$wgOut->addWikiText("==".wfMsg('mlGZNearbyLocationsHeading')."==\n".wfMsg('mlGZNearbyLocationsDescription').<<<EOT
			 [[Image:KI angle icon.png]]&nbsp;'''<span class='angleValue'>$this->angle</span>&nbsp;&middot;&nbsp;[[Image:KI distance icon.png]]&nbsp;$this->distance&nbsp;&middot;&nbsp;[[Image:KI elevation icon.png]]&nbsp;$this->elevation''':

EOT
			);

			$wgOut->addHtml('<table style="width: 80%; margin-top: 1em;" class="sortable">'."\n");
				$wgOut->addHtml("<thead>\n<tr><th>".wfMsg('mlGZLocationTableHeader')."</th><th>".wfMsg('mlGZCoordinateTableHeader')."</th><th>".wfMsg('mlGZRelativeDistanceTableHeader')."</th></tr>\n</thead>\n<tbody>\n");

			$wgOut->addHtml($tableResultString);

			$wgOut->addHtml("</tbody>\n</table>\n");

			$wgOut->addHtml('<script type="text/javascript">roundValues();</script>');
		}
	}

	private function retrieveListOfLocations() {
		$article =& getArticleFollowingRedirects(wfMsg('mlGZCoordinateListPage'));
		$content =& $article->getContent();

		$contentArray = preg_split('/[\n\r]+/', $content);

		$coords = array();

		foreach ($contentArray as $key=>$value) {
			// filter out invalid lines
			if (preg_match_all('/\|/', $value, $matches) != 3) {
				unset($contentArray[$key]);
				continue;
			}

			$line = explode('|', $value);

			$coords[] = new GreatZeroCoordinate(strval($line[0]), intval($line[1]), intval($line[2]), intval($line[3]));
		}

		return $coords;
	}

	private function findNearbyLocations() {
		$coords = $this->retrieveListOfLocations();

		// not unit-safe yet
		$inputCoord = new GreatZeroCoordinate('', $this->angle, $this->distance, $this->elevation);

		$this->compareLocation($inputCoord, $coords);
	}

	private function showMap() {
		global $wgOut;

		$coords = $this->retrieveListOfLocations();

		$gzMap = new GreatZeroMap($coords);

		$wgOut->addWikiText("==Map data==");

		$wgOut->addHtml($gzMap->buildImageURL());
		$wgOut->addHtml($gzMap->buildPointsArrayURL());
		$wgOut->addHtml("\n".'<script type="text/javascript" src="'.$wgScriptPath.'/extensions/mlGZCoordinatesMap.js"></script>'."\n");
	}
}

class GreatZeroCoordinate {
	public $location;
	public $angle;
	public $distance;
	public $elevation;
	public $x;
	public $y;
	public $z;
	
	public static function newFromTitle($title) {
		$article =& getArticleFollowingRedirects(wfMsg('mlGZCoordinateListPage'));
		$content =& $article->getContent();

		$contentArray = preg_split('/[\n\r]+/', $content);

		$coords = array();

		foreach ($contentArray as $key=>$value) {
			// filter out invalid lines
			if (preg_match_all('/\|/', $value, $matches) != 3) {
				unset($contentArray[$key]);
				continue;
			}

			$line = explode('|', $value);

			if (strcmp(strval($line[0]), $title) == 0) {
				return new GreatZeroCoordinate(strval($line[0]), intval($line[1]), intval($line[2]), intval($line[3]));
			}
		}
	}

	function __construct($a_location, $a_angle, $a_distance, $a_elevation) {
		$angle = $a_angle * 2 * pi() / 62500;

		$this->location = $a_location;
		$this->angle = $a_angle;
		$this->distance = $a_distance;
		$this->elevation = $a_elevation;
		$this->x = cos($angle) * $a_distance;
		$this->y = sin($angle) * $a_distance;
		$this->z = $a_elevation;
	}
	
	function distance_from($a_other) {
		return sqrt(pow($this->x - $a_other->x, 2) + pow($this->y - $a_other->y, 2) + pow($this->z - $a_other->z, 2));
	}

	function isEquivalentTo($a_other) {
		if (($this->angle == $a_other->angle) && ($this->distance == $a_other->distance) && ($this->elevation == $a_other->elevation)) {
			return true;
		} else {
			return false;
		}
	}
}

class GreatZeroMap {
	public $coordinates;
	public $width;
	public $height;
	public $shiftX;
	public $shiftY;

	function __construct($inCoordinates) {
		$this->coordinates = $inCoordinates;

		$this->determineWidth();
		$this->determineHeight();
	}

	function determineWidth() {
		$highest = 0;
		$lowest = 0;

		foreach($this->coordinates as $key=>$value) {
			if ($value->x > $highest) {
				$highest = $value->x;
			}

			if ($value->x < $lowest) {
				$lowest = $value->x;
			}
		}

		$this->shiftX = $lowest * -1;

		$this->width = (abs($highest)+abs($lowest));
	}

	function determineHeight() {
		$highest = 0;
		$lowest = 0;

		foreach($this->coordinates as $key=>$value) {
			if ($value->y > $highest) {
				$highest = $value->y;
			}

			if ($value->y < $lowest) {
				$lowest = $value->y;
			}

		}

		$this->shiftY = $lowest * -1;

		$this->height = (abs($highest)+abs($lowest));
	}

	function buildImageURL() {
		$pointsString = '?mode=mapImage&amp;size='.round($this->width).'|'.round($this->height);
		$i=1;

		foreach($this->coordinates as $key=>$value) {
			$pointsString .= '&amp;p'.$i.'='.round($value->x + $this->shiftX).'|'.round($value->y + $this->shiftY).'|'.urlencode($value->location);
			$i++;
		}

		return '<img src="/extensions/mlGZCoordinatesMap.php'.$pointsString.'" style="width: 80%;" id="mlGZMap" onmouseover="mlGZShowMapPointLabel(event, '.round($this->width).', '.round($this->height).');" />';
	}

	function buildPointsArrayURL() {
		$pointsString = '?mode=pointsArray&amp;size='.round($this->width).'|'.round($this->height);
		$i=1;

		foreach($this->coordinates as $key=>$value) {
			$pointsString .= '&amp;p'.$i.'='.round($value->x + $this->shiftX).'|'.round($value->y + $this->shiftY).'|'.urlencode($value->location);
			$i++;
		}

		return '<script type="text/javascript" src="/extensions/mlGZCoordinatesMap.php'.$pointsString.'" style="width: 80%;" id="mlGZMap" onmouseover="mlGZShowMapPointLabel(event, '.round($this->width).', '.round($this->height).');"></script>';
	}
}
?>
