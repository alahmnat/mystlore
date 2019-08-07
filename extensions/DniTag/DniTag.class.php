<?php
/**
 * This class handles formatting D'ni script in WikiText, specifically anything within
 * <d'ni></d'ni> tags.
 */

class DniTag {
	/**
	 * Bind the renderDniTag function to the <d'ni> tag
	 * @param Parser $parser
	 * @return bool true
	 */
	public static function onParserSetup( Parser &$parser ) {
		$parser->setHook( "dni", "DniTag::renderDniTag" );
		return true;
	}

	/**
	 * Parse the text to be wrapped in a span with the necessary class
	 * @param string $in The text inside the d'ni tag
	 * @param array $param
	 * @param Parser $parser
	 * @param boolean $frame
	 * @return string
	 */
	public static function renderDniTag( $input, array $param, Parser $parser, PPFrame $frame ) {
// 		return '<span class="dni">'.$in.'</span>';
		$attribs = array(
			'class' => 'dni'
		);
		$output = $parser->recursiveTagParse($input, $frame);
// 		return $input;
		return Html::rawElement('span', $attribs, trim($output));
// 		return Html::rawElement( 'div', $attribs, $newline . trim( $text ) . $newline );
	}
}
