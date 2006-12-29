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

	$array = preg_split('/[\n\r]+/', $input);

	foreach ($array as $key=>$value) {
		// remove items that don't start with a bullet point
		if (! preg_match('/^*/', $value)) {
			unset($array[$key]);
			continue;
		}
	}

	return "<div class=\"mlDidYouKnow\">".$array[array_rand($array)]."</div>";
}

function renderMainPagePurgeLink( $input, $argv, &$parser ) {
	srand((float) microtime() * 10000000);
	$rand = rand();

	return "<a href=\"http://www.mystlore.com/wiki?title=Main_Page&action=purge&rand=".$rand."\">$input</a>";
}

function renderDidYouKnowCount( $input, $argv, &$parser ) {
	$input = $parser->replaceVariables($input); // load the template

	$array = preg_split('/[\n\r]+/', $input);

	foreach ($array as $key=>$value) {
		// remove items that don't start with a bullet point
		if (! preg_match('/^*/', $value)) {
			unset($array[$key]);
			continue;
		}
	}

	return count($array);
}
?>
