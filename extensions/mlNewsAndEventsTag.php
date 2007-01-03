<?php

/*
	MYSTlore NewsAndEvents Tag for MediaWiki
	http://code.google.com/p/mystlore/

	Created 2006-12-17 by Soeren Kuklau

	Copyright 2006 MYSTlore contributors. Some rights reserved.

	Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.

	The full license can be retrieved at:
	http://www.opensource.org/licenses/mit-license.php
*/

$wgExtensionFunctions[] = "mlNewsAndEventsTag";
$wgHooks['ArticleSaveComplete'][] = "mlNewsAndEventsExporting" ;

function mlNewsAndEventsTag() {
	global $wgParser;

	$wgParser->setHook("AllEvents", "renderAllEvents");
	$wgParser->setHook("RecentEvents", "renderRecentEvents");
	$wgParser->setHook("UpcomingEvents", "renderUpcomingEvents");
	$wgParser->setHook("EventsCount", "renderEventsCount");
	$wgParser->setHook("EventsCalendarSubscribeLink", "renderEventsCalendarSubscribeLink");
	$wgParser->setHook("EventsCalendarDownloadLink", "renderEventsCalendarDownloadLink");
	$wgParser->setHook("EventsFeedSubscribeLink", "renderEventsFeedSubscribeLink");
	$wgParser->setHook("EventsFeedDownloadLink", "renderEventsFeedDownloadLink");
}

function mlNewsAndEventsExporting( &$article ) {
	$mTitle =& $article->getTitle();

	// only update if an NaE-related page was updated
	if (strstr($mTitle->getText(), 'News and Events')) {
		exportNAEiCalendar();
		exportNAERSS();
	}

	return true; // it'll assume an edit conflict otherwise
}

function exportNAEiCalendar() {
	global $wgParser;

	$naeTitle= Title::newFromURL('Template:News and Events');
	$naeTitle->invalidateCache();

	$naeArticle = new Article($naeTitle);
	$naeText =& $naeArticle->getContent();

	$wgParser->OutputType(OT_WIKI);
	$pText =& $wgParser->parse($naeText, $naeTitle,
		new ParserOptions(), false, true);

	$text = $pText->getText();

	$text = preg_replace('/<\/h2>/', '\\1</h2>
', $text); // make sure there's a linebreak after every monthly header

	$array = preg_split('/[\n\r]+/', $text);

	$icsOutput = "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//MYSTlore/MYSTlore//NONSGML v1.0//EN
";

	foreach( $array as $key => $value ) {
		if (($value == '</li></ul>') || ! (preg_match('/[A-Za-z0-9]+/', $value))) {
			unset($array[$key]);
			continue;
		}

		if (preg_match('/<div(.*)<h2>(.*)<\/h2>$/', $value, $matches)) { // monthly headers
			$month = explode(' ', $matches[2]); // array with {month,year}

			$months = array('January'=>'01', 'February'=>'02', 'March'=>'03', 'April'=>'04', 'May'=>'05', 'June'=>'06', 'July'=>'07', 'August'=>'08', 'September'=>'09', 'October'=>'10', 'November'=>'11', 'December'=>'12');

			$month[0] = $months[$month[0]]; // switch from month names to month numbers
		}
		else  { // not a monthly header; presumed an event
			$line = explode(': ', $value);
			preg_match('/[0-9]{2}/', $line[0], $matches);
			unset($line[0]);

			$icsOutput .= "BEGIN:VEVENT
";

			$result = strip_tags(implode(': ',$line));

			// parse per-event categories
			$categories = preg_split('/\(\((.*?)\)\)/', $result, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
			unset($categories[0]);
			if (count($categories) > 0) {
				$icsOutput .= "CATEGORIES:";
			}
			foreach($categories as $key=>$value) {
				if (! (preg_match('/[A-Za-z0-9]+/', $value))) {
					unset($categories[$key]);
					continue;
				}
				$icsOutput .= $value.",";
			}
			$icsOutput = preg_replace('/,$/','
',$icsOutput); // get rid of the last delimiter and add linebreak

			$result = preg_replace('/\(\((.*?)\)\)/','',$result);

			$icsOutput .= "UID:".md5($result)."@mystlore.com
DTSTART:".$month[1].$month[0].$matches[0]."
SUMMARY:".$result."
END:VEVENT
";
		}
	}

	$icsOutput .= "END:VCALENDAR
";

	$icsFile = fopen('/data/www/chucker/myst/community/docs/wiki/Events.ics', 'w');
	fwrite($icsFile, $icsOutput);
	fclose($icsFile);
}

function exportNAERSS() {
	global $wgParser;

	$naeTitle= Title::newFromURL('Template:News and Events');
	$naeTitle->invalidateCache();

	$naeArticle = new Article($naeTitle);
	$naeText =& $naeArticle->getContent();

	$wgParser->OutputType(OT_WIKI);
	$pText =& $wgParser->parse($naeText, $naeTitle,
		new ParserOptions(), false, true);

	$text = $pText->getText();

	$text = preg_replace('/<\/h2>/', '\\1</h2>
', $text); // make sure there's a linebreak after every monthly header

	$array = preg_split('/[\n\r]+/', $text);

	$rssOutput = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
<rss version=\"2.0\">
	<channel>
		<title>MYSTlore News and Events</title>
		<link>http://www.mystlore.com/</link>
		<description>News and Events for 'MYSTlore', a wiki for the Myst universe</description>
		<language>en</language>
		<copyright>Contributions licensed under the CC by-nc 2.5 license, unless otherwise specified. Refer to http://www.mystlore.com/wiki/MYSTlore:Legal_notice for more information.</copyright>

		<webMaster>chucker@mystfans.com (Soeren Nils 'chucker' Kuklau</webMaster>
		<docs>http://blogs.law.harvard.edu/tech/rss</docs>
		<ttl>60</ttl>

";

	foreach( $array as $key => $value ) {
		if (($value == '</li></ul>') || ! (preg_match('/[A-Za-z0-9]+/', $value))) {
			unset($array[$key]);
			continue;
		}

		if (preg_match('/<div(.*)<h2>(.*)<\/h2>$/', $value, $matches)) { // monthly headers
			$month = explode(' ', $matches[2]); // array with {month,year}

			$months = array('January','01', 'February','02', 'March'=>'03', 'April'=>'04', 'May'=>'05', 'June'=>'06', 'July'=>'07', 'August'=>'08', 'September'=>'09', 'October'=>'10', 'November'=>'11', 'December'=>'12');

			$month[0] = $months[$month[0]]; // switch from month names to month numbers
		}
		else  { // not a monthly header; presumed an event
			$line = explode(': ', $value);
			preg_match('/[0-9]{2}/', $line[0], $matches);
			unset($line[0]);

			$day = $month[1].'-'.$month[0].'-'.$matches[0];

			$rssOutput .= "		<item>
";

			$result=strip_tags(implode(': ',$line));

			// parse per-event categories
			$categories = preg_split('/\(\((.*?)\)\)/', $result, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
			unset($categories[0]);
			foreach($categories as $key=>$value) {
				if (! (preg_match('/[A-Za-z0-9]+/', $value))) {
					unset($categories[$key]);
					continue;
				}
				$rssOutput .= "			<category>".$value."</category>
";
			}
			$result = preg_replace('/\(\((.*?)\)\)/','',$result);

			// strictly speaking, these are not the /publication/ dates...
			// future dates (for upcoming scheduled events) may cause some readers to ignore those items. It is not a spec violation, however.
			$rssOutput .= "			<pubDate>".strftime("%a, %d %b %Y %T %z", strtotime($day))."</pubDate>
			<guid isPermaLink=\"false\">".md5($result)."</guid>
			<description>".$result."</description>
		</item>
";
		}
	}

	$rssOutput .= "	</channel>
</rss>
";

	$rssFile = fopen('/data/www/chucker/myst/community/docs/wiki/Events.rss', 'w');
	fwrite($rssFile, $rssOutput);
	fclose($rssFile);
}

function renderAllEvents( $input, $argv, &$parser ) {
	// $input = $parser->replaceVariables($input); // load the template
	// $input = $parser->replaceInternalLinks($input); // parse wiki links
	// $input = $parser->replaceExternalLinks($input); // parse external links
	// $input = $parser->doQuotes($input); // parse quote-based emphasis
	// $input = $parser->formatHeadings($input); // parse monthly headers
	$input = $parser->internalParse($input);

	$array = preg_split('/[\n\r]+/', $input);

	foreach( $array as $key => $value ) {
		// parse per-event categories
		$value = preg_replace('/\(\((.*?)\)\)/','<sup><strong>\\1</strong> • </sup>',$value);
		$value = preg_replace('/ • <\/sup>$/','</sup>',$value); // get rid of the last occurence
		$array[$key] = $value;
	}

	if (count($array) == 0) {
		return "None.";
	}

	return implode('
', $array);
}

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
			continue;
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
				continue;
			}

			// parse per-event categories
			$value = preg_replace('/\(\((.*?)\)\)/','<sup><strong>\\1</strong> • </sup>',$value);
			$value = preg_replace('/ • <\/sup>$/','</sup>',$value); // get rid of the last occurence
			$array[$key] = $value;
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
			continue;
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
				continue;
			}

			// parse per-event categories
			$value = preg_replace('/\(\((.*?)\)\)/','<sup><strong>\\1</strong> • </sup>',$value);
			$value = preg_replace('/ • <\/sup>$/','</sup>',$value); // get rid of the last occurence
			$array[$key] = $value;
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

function renderEventsFeedSubscribeLink( $input, $argv, &$parser ) {
	return "<a href=\"feed://www.mystlore.com/Events.rss\">".$input."</a>";
}

function renderEventsFeedDownloadLink( $input, $argv, &$parser ) {
	return "<a href=\"http://www.mystlore.com/Events.rss\">".$input."</a>";
}

?>
