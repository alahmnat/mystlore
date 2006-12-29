<?php

// MYSTlore DidYouKnow Tag for MediaWiki

$wgExtensionFunctions[] = "mlDidYouKnowTag";

function mlDidYouKnowTag() {
	global $wgParser;

	$wgParser->setHook("DidYouKnow", "renderDidYouKnow");
	$wgParser->setHook("MainPagePurgeLink", "renderMainPagePurgeLink");
	$wgParser->setHook("DidYouKnowCount", "renderDidYouKnowCount");
}

function renderDidYouKnow( $input, $argv, &$parser ) {
	srand((float) microtime() * 10000000);

	$input = $parser->replaceVariables($input); // load the template
	$input = $parser->replaceInternalLinks($input); // parse wiki links
	$input = $parser->doQuotes($input); // parse quote-based emphasis

	$array = explode("*", $input);
	unset($array[0]); // FIXME: this should be replaced with code that removes any improper lines (e.g., ones that don't start with *)

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
