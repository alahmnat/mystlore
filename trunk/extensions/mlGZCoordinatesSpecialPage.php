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
$wgHooks['ArticleSaveComplete'][] = "mlGZAddCoordinate";

require_once( "$IP/includes/SpecialPage.php" );

function mlGZCoordinatesSpecialPageLoader() {
	global $IP, $wgMessageCache;
	
	$wgMessageCache->addMessages(array('gzcoordinatesspecialpage' => 'Great Zero Coordinates'));
	
	SpecialPage::addPage( new GZCoordinatesSpecialPage() );
}

function mlGZAddCoordinate(&$editedArticle) {
	$editedTitle =& $editedArticle->getTitle();
	if ($editedTitle->getNamespace() != NS_MAIN) {
		return true;
	}

	if (preg_match('/{{gz-coord.*}}/', $editedArticle->getContent(), $templateMatches)) { // only update if the article has such a template
		$gzCoordListPageTitle = Title::newFromURL('List of known GZ coordinates');
		$gzCoordListPageTitle->invalidateCache();

		$gzCoordListPageArticle = new Article($gzCoordListPageTitle);
		$gzCoordListPageContent =& $gzCoordListPageArticle->getContent();

		$contentArray = preg_split('/[\n\r]+/', $gzCoordListPageContent);

		$commentLine = $contentArray[0];

		unset($contentArray[0]);

		$addedBit = '';
		
		foreach ($contentArray as $key=>$value) { // ensure only one line per article
			$line = explode('|', $value);

			if (strcmp($line[0], $editedTitle->getText()) == 0) {
				unset($contentArray[$key]);
			}

			$addedBit .= "\n".$contentArray[$key];
		}

		$coords = explode('|', $templateMatches[0]);
		$addedBit .= "\n".$editedTitle->getText().'|'.intval($coords[1]).'|'.intval($coords[2]).'|'.intval($coords[3]);

		$gzCoordListPageArticle->doEdit($commentLine.$addedBit, '[mlGZCoordinates automated addition of Great Zero coordinate]', EDIT_UPDATE);
	}

	return true;
}

class GZCoordinatesSpecialPage extends SpecialPage {
	function GZCoordinatesSpecialPage() {
		SpecialPage::SpecialPage('GZCoordinatesSpecialPage');
	}

	function execute( $par ) {
		global $wgUser, $wgOut, $wgTitle, $wgRequest;

		$this->setHeaders();

		$this->angle = $wgRequest->getText('angle');
		$this->distance = $wgRequest->getText('distance');
		$this->elevation = $wgRequest->getText('elevation');

		$this->map = $wgRequest->getText('map');

		$this->angleUnit = $wgRequest->getText('angleUnit');

		$wgOut->addHtml( $this->makeForm() );

		if ((strlen($this->angle) > 0) && (strlen($this->distance) > 0) && (strlen($this->elevation) > 0)) {
			$this->findNearbyLocations();
		}

		if (strcmp($this->map, "showZOMG") == 0) {
			$this->showMap();
		}
	}

	private function getHumanUnit($unit, $value) {
		global $wgOut;

		// TODO: localization!

		switch ($unit) {
			case 'toran':
				if ($value != 1) {
					$result = 'torantee';
				}
				else $result = $unit;

				break;

			case 'degree':
				if ($value != 1) {
					$result = 'degrees';
				}
				else $result = $unit;

				break;

			case 'shahfee':
				if ($value != 1) {
					$result = 'shahfeetee';
				}
				else $result = $unit;

				break;

			case 'foot':
				if ($value != 1) {
					$result = 'feet';
				}
				else $result = $unit;

				break;

			case 'meter':
				if ($value != 1) {
					$result = 'meters';
				}
				else $result = $unit;

				break;
		}

		return $wgOut->parse($result, false);
	}

	// should use wfMsg and wfMsgHtml to localize this stuff
	private function makeForm() {
		global $wgScript, $wgOut;
		$title = self::getTitleFor( 'GZCoordinatesSpecialPage' );
		$form  = '<fieldset><legend>' . "Search for nearby locations" . '</legend>';
		$form .= Xml::openElement( 'form', array( 'method' => 'get', 'action' => $wgScript ) );
		$form .= Xml::hidden( 'title', $title->getPrefixedText() );
		$form .= '<p>';
		$form .= $wgOut->parse('[[Image:KI_angle_icon.png]]', false);
		$form .= '&nbsp;' . Xml::inputLabel("Angle:", 'angle', 'angle', 10, $this->angle) . '&nbsp;';

		$form .= Xml::openElement('select', array('name' => "angleUnit", 'id' => "angleUnit", 'onchange' => "changeAngleUnit();"));
		if (strcmp($this->angleUnit, "degrees") == 0) {
			$form .= Xml::option($this->getHumanUnit("toran", 0));
			$form .= Xml::option($this->getHumanUnit("degree", 0), '', true);
		} else {
			$form .= Xml::option($this->getHumanUnit("toran", 0), '', true);
			$form .= Xml::option($this->getHumanUnit("degree", 0));
		}
		$form .= Xml::closeElement('select') . '<span style="padding-right: 2em;">&nbsp;</span>';

		$form .= $wgOut->parse('[[Image:KI_distance_icon.png]]', false);
		$form .= '&nbsp;' . Xml::inputLabel("Distance:", 'distance', 'distance', 10, $this->distance) . '<span style="padding-right: 2em;">&nbsp;'.$this->getHumanUnit("shahfee", 0).'&nbsp;</span>';
		$form .= $wgOut->parse('[[Image:KI_elevation_icon.png]]', false);
		$form .= '&nbsp;' . Xml::inputLabel("Elevation:", 'elevation', 'elevation', 10, $this->elevation) . '<span style="padding-right: 2em;">&nbsp;'.$this->getHumanUnit("shahfee", 0).'&nbsp;</span>';
		$form .= Xml::submitButton( "Go" ) . '</p>';
		$form .= Xml::closeElement( 'form' );
		$form .= '</fieldset>';

		$form .= '<script type="text/javascript" src="'.$wgScriptPath.'/extensions/mlGZCoordinatesSpecialPage.js">';

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
			$resultString .= "*'''[[".$value->location."]]''', at {{gz-coord|".$value->angle.'|'.$value->distance.'|'.$value->elevation."}}; <span class='distanceFromValue'>".$key."</span> ".$this->getHumanUnit('shahfee', $key)." away\n";
		}

		if (strcmp($resultString, '') != 0) {
			$wgOut->addWikiText(<<<EOT
==Nearby locations==
The following locations are in proximity of your given coordinates [[Image:KI angle icon.png]]&nbsp;'''<span class='angleValue'>$this->angle</span>&nbsp;&middot;&nbsp;[[Image:KI distance icon.png]]&nbsp;$this->distance&nbsp;&middot;&nbsp;[[Image:KI elevation icon.png]]&nbsp;$this->elevation''', with the closest first:

EOT
.$resultString);

		$wgOut->addHtml('<script type="text/javascript">roundValues();</script>');
		}
	}

	private function retrieveListOfLocations() {
		$gzCoordListPageTitle = Title::newFromURL('List of known GZ coordinates');
		$gzCoordListPageTitle->invalidateCache();

		$gzCoordListPageArticle = new Article($gzCoordListPageTitle);
		$gzCoordListPageContent =& $gzCoordListPageArticle->getContent();

		$contentArray = preg_split('/[\n\r]+/', $gzCoordListPageContent);

		unset($contentArray[0]);
		unset($contentArray[1]);
		unset($contentArray[0]);

		$coords = array();

		foreach ($contentArray as $key=>$value) {
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

		$wgOut->addWikiText($gzMap->width());
		$wgOut->addWikiText($gzMap->height());
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

	function __construct($inCoordinates) {
		$this->coordinates = $inCoordinates;
	}

	function width() {
		if (! isset($this->height)) {
			$this->determineWidth();
		}

		return $this->width;
	}

	function height() {
		if (! isset($this->height)) {
			$this->determineHeight();
		}

		return $this->height;
	}

	function determineWidth() {
		global $wgOut;

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

		$this->width = (abs($highest)+abs($lowest));
	}

	function determineHeight() {
		global $wgOut;

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

		$this->height = (abs($highest)+abs($lowest));
	}
}
?>
