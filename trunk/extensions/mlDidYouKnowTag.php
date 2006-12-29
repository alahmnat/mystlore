<?php

// MYSTlore DidYouKnow Tag for MediaWiki

// 1.1.0, Soeren Kuklau, 2006-12-08
// Internal link and quotes parsing

// 1.0.0, Soeren Kuklau, 2006-12-08
// Initial version

//require_once( 'includes/Parser.php' );

$wgExtensionFunctions[] = "mlDidYouKnowTag";

function mlDidYouKnowTag() {
	global $wgParser;

	$wgParser->setHook("DidYouKnow", "renderDidYouKnow");
	$wgParser->setHook("MainPagePurgeLink", "renderMainPagePurgeLink");
	$wgParser->setHook("DidYouKnowCount", "renderDidYouKnowCount");
}

function renderDidYouKnow( $input, $argv, &$parser ) {
	srand((float) microtime() * 10000000);

	//return "test";

	$input = $parser->replaceVariables($input); // load the template
	$input = $parser->replaceInternalLinks($input); // parse wiki links
	$input = $parser->doQuotes($input); // parse quote-based emphasis

	$array = explode("*", $input);
	unset($array[0]); // FIXME: this should be replaced with code that removes any improper lines (e.g., ones that don't start with *)

//	$output = implode("--", $array);
	$output .= $array[array_rand($array)];

	return "<div class=\"mlDidYouKnow\">".$output."</div>";
}

function renderMainPagePurgeLink( $input, $argv, &$parser ) {
	srand((float) microtime() * 10000000);
	$rand = rand();

	return "<a href=\"http://www.mystlore.com/wiki?title=Main_Page&action=purge&rand=".$rand."\">$input</a>";
}

function renderDidYouKnowCount( $input, $argv, &$parser ) {
	$input = $parser->replaceVariables($input); // load the template
	$array = explode("*", $input);
	unset($array[0]); // FIXME: this should be replaced with code that removes any improper lines (e.g., ones that don't start with *)

	return count($array);
}
?>
