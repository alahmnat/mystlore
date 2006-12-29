<?php

// MYSTlore DidYouKnow Tag for MediaWiki

$wgExtensionFunctions[] = "mlDidYouKnowTag";
$wgHooks['ArticleSaveComplete'][] = "mlDidYouKnowExporting" ;

function mlDidYouKnowTag() {
	global $wgParser;

	$wgParser->setHook("DidYouKnow", "renderDidYouKnow");
	$wgParser->setHook("MainPagePurgeLink", "renderMainPagePurgeLink");
	$wgParser->setHook("DidYouKnowCount", "renderDidYouKnowCount");
}

function mlDidYouKnowExporting( &$article ) {
	$mTitle =& $article->getTitle();

	$rssFile = fopen('/data/www/chucker/myst/community/docs/wiki/DidYouKnow.rss', 'w');
	fwrite($rssFile, "foo");
	fclose($rssFile);
	// only update if a DYK-related page was updated
	if (strstr($mTitle->getText(), 'DidYouKnow') || strstr($mTitle->getText(), 'Did You Know')) {
		exportDYKRSS();
	}

	return true; // it'll assume an edit conflict otherwise
}

function exportDYKRSS() {
	global $wgParser;

	$naeTitle= Title::newFromURL('Template:Did You Know');
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
		<title>MYSTlore Did You Know</title>
		<link>http://www.mystlore.com/</link>
		<description>Did You Know? section of tidbits for 'MYSTlore', a wiki for the Myst universe</description>
		<language>en</language>
		<copyright>Contributions licensed under the CC by-nc 2.5 license, unless otherwise specified. Refer to http://www.mystlore.com/wiki/MYSTlore:Legal_notice for more information.</copyright>

		<webMaster>chucker@mystfans.com (Soeren Nils 'chucker' Kuklau</webMaster>
		<docs>http://blogs.law.harvard.edu/tech/rss</docs>
		<ttl>60</ttl>

";

	srand((double) microtime() * 1000000);

	foreach( $array as $key => $value ) {
		// if it's not a list item, throw it out
		if (! (preg_match('/<li>/', $value))) {
			unset($array[$key]);
			continue;
		}

		$rssOutput .= "		<item>
";
		$rssOutput .= "			<guid isPermaLink=\"false\">".md5(getmypid().uniqid(rand()).$_SERVER['SERVER_NAME'])."</guid>
			<description>".strip_tags($value)."</description>
		</item>
";
	}

	$rssOutput .= "	</channel>
</rss>
";

	$rssFile = fopen('/data/www/chucker/myst/community/docs/wiki/DidYouKnow.rss', 'w');
	fwrite($rssFile, $rssOutput);
	fclose($rssFile);
}

function renderDidYouKnow( $input, $argv, &$parser ) {
	srand((float) microtime() * 10000000);

	$input = $parser->replaceVariables($input); // load the template
	$input = $parser->replaceInternalLinks($input); // parse wiki links
	$input = $parser->doQuotes($input); // parse quote-based emphasis

	$array = preg_split('/[\n\r]+/', $input);

	foreach ($array as $key=>$value) {
		// remove items that don't start with a bullet point
		if (! preg_match('/^\*/', $value)) {
			unset($array[$key]);
			continue;
		}
		$array[$key] = preg_replace('/^[\*\ ]+/', '', $value);
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
		if (! preg_match('/^\*/', $value)) {
			unset($array[$key]);
			continue;
		}
	}

	return count($array);
}
?>
