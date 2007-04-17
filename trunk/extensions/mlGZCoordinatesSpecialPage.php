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

require_once( "$IP/includes/SpecialPage.php" );

function mlGZCoordinatesSpecialPageLoader() {
	global $IP, $wgMessageCache;
	
	$wgMessageCache->addMessages(array('gzcoordinatesspecialpage' => 'Great Zero Coordinates'));
	
	SpecialPage::addPage( new GZCoordinatesSpecialPage() );
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

		$this->show = $wgRequest->getText('show');

		$this->angleUnit = $wgRequest->getText('angleUnit');

		$wgOut->addWikiText("This is some ''lovely'' [[wikitext]] that will '''get''' parsed nicely.");
		$wgOut->addHtml( $this->makeForm() );

		if ((strlen($this->angle) > 0) && (strlen($this->distance) > 0) && (strlen($this->elevation) > 0)) {
			$this->findNearbyLocations();
		}

		if (strcmp($this->show, "all") == 0) {
			$this->showAllLocations();
		}
	}

	// should use wfMsg and wfMsgHtml to localize this stuff
	private function makeForm() {
		global $wgScript, $wgOut;
		$title = self::getTitleFor( 'GZCoordinatesSpecialPage' );
		$form  = '<fieldset><legend>' . "Search for nearby locations" . '</legend>';
		$form .= Xml::openElement( 'form', array( 'method' => 'get', 'action' => $wgScript ) );
		$form .= Xml::hidden( 'title', $title->getPrefixedText() );
		$form .= '<p>' . Xml::inputLabel("Angle:", 'angle', 'angle', 10, $this->angle);

		$form .= Xml::openElement('select', array('name' => "angleUnit", 'id' => "angleUnit", 'onchange' => "changeAngleUnit();"));
		if (strcmp($this->angleUnit, "degrees") == 0) {
			$form .= Xml::option("torantee");
			$form .= Xml::option("degrees", '', true);
		} else {
			$form .= Xml::option("torantee", '', true);
			$form .= Xml::option("degrees");
		}
		$form .= Xml::closeElement('select') . '<span style="padding-right: 2em;">&nbsp;</span>';

		$form .= Xml::inputLabel("Distance:", 'distance', 'distance', 10, $this->distance) . '<span style="padding-right: 2em;">&nbsp;</span>';
		$form .= Xml::inputLabel("Elevation:", 'elevation', 'elevation', 10, $this->elevation) . '<span style="padding-right: 2em;">&nbsp;</span>';
		$form .= Xml::submitButton( "Go" ) . '</p>';
		$form .= Xml::closeElement( 'form' );
		$form .= '</fieldset>';

		$form .= <<<EOT
<script type="text/javascript">
function changeAngleUnit() {
	angleUnitConversionFactor = 62500/360;

	var spanElements = document.getElementsByTagName("span");
	var i, len = spanElements.length;

	switch (document.getElementById('angleUnit').selectedIndex) {
		case 0: // degrees to torantee
			document.getElementById('angle').value *= angleUnitConversionFactor;

			for (i = 0; i < len; i++) {
				var elem = spanElements[i];
				if (elem.className == "angleValue") {
					elem.innerHTML *= angleUnitConversionFactor;
				}
			}

			break;

		case 1: // torantee to degrees
			document.getElementById('angle').value /= angleUnitConversionFactor;

			for (i = 0; i < len; i++) {
				var elem = spanElements[i];
				if (elem.className == "angleValue") {
					elem.innerHTML /= angleUnitConversionFactor;
				}
			}

			break;
	}
}
</script>
EOT;

		return $form;
	}

	private function compareLocation() {
		global $wgOut;

		if (strcmp($this->angleUnit, "degrees") == 0) {
			$useDegrees = true;
		}

		$locations = array(
			array(
				'title' => "Tokotah Courtyard",

				'angle' => 12,
				'distance' => 34,
				'elevation' => 56
			),

			array(
				'title' => "Canyon Mall",

				'angle' => 583,
				'distance' => 293,
				'elevation' => 5902
			)
		);

		foreach ($locations as $value) {
			if ($useDegrees) {
				$value['angle'] = $value['angle'] / (62500/360);
			}

			$resultString .= "*'''[[".$value['title']."]]''', at [{{SERVER}}{{localurl:Special:GZCoordinatesSpecialPage|angle=".$value['angle']."&distance=".$value['distance']."&elevation=".$value['elevation']."&angleUnit=".$this->angleUnit." <span class='angleValue'>".$value['angle'].'</span>&deg; - '.$value['distance'].' - '.$value['elevation']."}}]\n";
		}

		$wgOut->addWikiText($resultString);
	}

	private function findNearbyLocations() {
		global $wgOut;

		$wgOut->addWikiText("==Nearby locations==");
		$wgOut->addWikiText("The following locations are in proximity of your given coordinates '''<span class='angleValue'>".$this->angle."</span>&deg; - ".$this->distance." - ".$this->elevation."''':");
		$wgOut->addWikiText("*The garden
*The living room
*The neighbor's house");

		$this->compareLocation();
	}

	private function showAllLocations() {
		global $wgOut;

		$wgOut->addWikiText("==All stored locations==");
	}
}

?>
