<?php

// MYSTlore NewsAndEvents Tag for MediaWiki
//
// 1.2, Soeren Kuklau, 2006-12-27
// Make it react to any page save for now, but always export the same page
//
// 1.1.1, Soeren Kuklau, 2006-12-25
// Make it not react to changes in the MYSTlore namespace
// 
// 1.1, Soeren Kuklau, 2006-12-25
// iCalendar (vCalendar, .ics) exporting
// Support for external links
//
// 1.0, Soeren Kuklau, 2006-12-21
// No known issues
//
// 1.0b1, Soeren Kuklau, 2006-12-21
// known bug: renderEventsCount() includes monthly headers
//
// 1.0a1, Soeren Kuklau, 2006-12-21
// different naming scheme
// renderEventsCount() mostly implemented
// initial renderRecentEvents() and renderUpcomingEvents() stuff
//
// 1.0d1, Soeren Kuklau, 2006-12-17
// Initial version

//require_once( 'includes/Parser.php' );

$wgExtensionFunctions[] = "mlNewsAndEventsTag";
$wgHooks['ArticleSave'][] = "mlNewsAndEventsExporting" ;

function mlNewsAndEventsTag() {
	global $wgParser;

//	$wgParser->setHook("AllEvents", "renderAllEvents");
	$wgParser->setHook("RecentEvents", "renderRecentEvents");
	$wgParser->setHook("UpcomingEvents", "renderUpcomingEvents");
	$wgParser->setHook("EventsCount", "renderEventsCount");
	$wgParser->setHook("EventsCalendarSubscribeLink", "renderEventsCalendarSubscribeLink");
	$wgParser->setHook("EventsCalendarDownloadLink", "renderEventsCalendarDownloadLink");
}

function mlNewsAndEventsExporting( &$article ) {
	global $wgParser;

	$mTitle =& $article->getTitle();
	$mText =& $article->getContent();

//	if (strstr($mTitle->getText(), 'News and Events')) {
		$naeTitle= Title::newFromURL('Template:News and Events');

		$naeArticle =& new Article($naeTitle);
		$naeText =& $naeArticle->getContent();

		$wgParser->disableCache();
		$wgParser->OutputType(OT_WIKI);
		$pText = $wgParser->parse($naeText, $naeTitle,
			new ParserOptions(), false, false);

		$text = $pText->getText();

		$text = preg_replace('/<\/h2>/', '\\1</h2>
', $text); // make sure there's a linebreak after every monthly header

		$array = preg_split('/[\n\r]+/', $text);

		$icsOutput = "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//MYSTlore/MYSTlore//NONSGML v1.0//EN
";

		$testOutput = '';

		$testOutput .= $naeTitle->getText().'
';

		foreach( $array as $key => $value ) {
			if (($value == '</li></ul>') || ! (preg_match('/[A-Za-z0-9]+/', $value))) {
				unset($array[$key]);
				continue;
			}

			$testOutput .= $key.'
'.$value.'
';

			if (preg_match('/<div(.*)<h2>(.*)<\/h2>$/', $value, $matches)) { // monthly headers
				$month = explode(' ', $matches[2]); // array with {month,year}

				$months = array('January','01', 'February','02', 'March'=>'03', 'April'=>'04', 'May'=>'05', 'June'=>'06', 'July'=>'07', 'August'=>'08', 'September'=>'09', 'October'=>'10', 'November'=>'11', 'December'=>'12');

				$month[0] = $months[$month[0]]; // switch from month names to month numbers
			}
			else  { // not a monthly header; presumed an event
				$line = explode(': ', $value);
				preg_match('/[0-9]{2}/', $line[0], $matches);
				unset($line[0]);

				$icsOutput .= "BEGIN:VEVENT
DTSTART:".$month[1].$month[0].$matches[0]."
SUMMARY:".strip_tags(implode(': ', $line))."
END:VEVENT
";
			}
		}

		$icsOutput .= "END:VCALENDAR
";

		$testFile = fopen('/data/www/chucker/myst/community/docs/wiki/testfile', 'w');
		fwrite($testFile, $testOutput);
		fclose($testFile);

		$icsFile = fopen('/data/www/chucker/myst/community/docs/wiki/Events.ics', 'w');
		fwrite($icsFile, $icsOutput);
		fclose($icsFile);
//	}

	return true; // it'll assume an edit conflict otherwise
}

/* function renderAllEvents( $input, $argv, &$parser ) { // not implemented
} */

function renderRecentEvents( $input, $argv, &$parser ) {
	$maxItems =	$argv['maxitems'];
	$maxDays =	$argv['maxdays'];
	$maxTime = time() - 60 * 60 * 24 * $maxDays;

	$input = $parser->replaceVariables($input); // load the template
	$input = $parser->replaceInternalLinks($input); // parse wiki links
	$input = $parser->replaceExternalLinks($input); // parse external links
	$input = $parser->doQuotes($input); // parse quote-based emphasis

	$array = preg_split('/[\n\r]+/', $input);

	foreach( $array as $key => $value ) {
		if (preg_match('/^==(.*)==$/', $value, $matches)) { // monthly headers
			$month = explode(' ', $matches[1]); // array with {month,year}

			unset($array[$key]);
		}
		else  { // not a monthly header; presumed an event]
			$line = explode(': ', $value);
			preg_match('/[0-9]{2}/', $line[0], $matches);

			$day = $month[0].' '.$matches[0].' '.$month[1]; // month, day, year

			// if it's after right now (in the future)
			// OR
			// if it's beyond the maximum amount of days in the past,
			// then we don't want it displayed
			if (strtotime($day) >= time() || strtotime($day) < $maxTime) {
				unset($array[$key]);
			}
		}
	}

	if (count($array) == 0) {
		return "None.";
	}

	// NB: this keeps the /last/ $maxItems items, as expected --
	// i.e., events closest to /right now/
	return implode('
', array_slice($array, -$maxItems, $maxItems));
}

function renderUpcomingEvents( $input, $argv, &$parser ) {
	$maxItems =	$argv['maxitems'];
	$maxDays =	$argv['maxdays'];
	$maxTime = time() + 60 * 60 * 24 * $maxDays;

	$input = $parser->replaceVariables($input); // load the template
	$input = $parser->replaceInternalLinks($input); // parse wiki links
	$input = $parser->replaceExternalLinks($input); // parse external links
	$input = $parser->doQuotes($input); // parse quote-based emphasis

	$array = preg_split('/[\n\r]+/', $input);

	foreach( $array as $key => $value ) {
		if (preg_match('/^==(.*)==$/', $value, $matches)) { // monthly headers
			$month = explode(' ', $matches[1]); // array with {month,year}

			unset($array[$key]);
		}
		else  { // not a monthly header; presumed an event]
			$line = explode(': ', $value);
			preg_match('/[0-9]{2}/', $line[0], $matches);

			$day = $month[0].' '.$matches[0].' '.$month[1]; // month, day, year

			// if it's before right now (in the past)
			// OR
			// if it's beyond the maximum amount of days in the future,
			// then we don't want it displayed
			if (strtotime($day) < time() || strtotime($day) > $maxTime) {
				unset($array[$key]);
			}
		}
	}

	if (count($array) == 0) {
		return "None.";
	}

	return implode('
', array_slice($array, 0, $maxItems));
}

function renderEventsCount( $input, $argv, &$parser ) {
	$input = $parser->replaceVariables($input); // load the template
	$input = $parser->replaceInternalLinks($input); // parse wiki links
	$input = $parser->replaceExternalLinks($input); // parse external links
	$input = $parser->doQuotes($input); // parse quote-based emphasis

	$array = preg_split('/[\n\r]+/', $input);

	foreach( $array as $key => $value ) {
		if (preg_match('/^==(.*)==$/', $value)) { // monthly headers
			unset($array[$key]);
		}
	}

	return count($array);
}

function renderEventsCalendarSubscribeLink( $input, $argv, &$parser ) {
	return "<a href=\"webcal://www.mystlore.com/Events.ics\">".$input."</a>";
}

function renderEventsCalendarDownloadLink( $input, $argv, &$parser ) {
	return "<a href=\"http://www.mystlore.com/Events.ics\">".$input."</a>";
}

?>
