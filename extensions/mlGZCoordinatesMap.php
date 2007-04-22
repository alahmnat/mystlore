<?php
// TODO: security: verify somehow that the referer is a MediaWiki installation

$query = explode('&', $_SERVER['QUERY_STRING']);

foreach($query as $value) {
	$queryValue = explode('=', $value);

	if (strcmp($queryValue[0], 'mode') == 0) {
		$mode = $queryValue[1];

		array_shift($query);
	}

	if (strcmp($queryValue[0], 'size') == 0) {
		$sizeValue = explode('|', $queryValue[1]);

		$width = $sizeValue[0];
		$height = $sizeValue[1];
		array_shift($query);
	}
}

switch ($mode) {
	case 'mapImage':
		mapImage($query, $width, $height);
		break;

	case 'pointsArray':
		pointsArray($query, $width, $height);
		break;
}

function mapImage ($query, $width, $height) {
	header ("Content-type: image/png");

	$width = $width+20;
	$height = $height+20;

	$im = @imagecreatetruecolor($width, $height)
	      or die("Cannot Initialize new GD image stream");

	$bgColor = imagecolorallocate($im, 249, 249, 249);
	imagefilledrectangle($im, 0, 0, $width-1, $height-1, $bgColor);

	$text_color = imagecolorallocate($im, 0, 43, 184);

	foreach($query as $value) {
		$queryValue = explode('=', $value);

		$pointValue = explode('|', $queryValue[1]);

		$x = $pointValue[0]+10;
		$y = $pointValue[1]+10;
		$location = urldecode($pointValue[2]);

		imageellipse($im, $x, $y, 5, 5, $text_color);
//		imagestring($im, 3, $x, $y, $location, $text_color);
	}

	imagepng($im);
	imagedestroy($im);
}

function pointsArray ($query, $width, $height) {
	header ("Content-type: text/javascript");

	echo <<<EOT
mlGZCoordinates = [
EOT
;

	$i = 0;
	foreach($query as $value) {
		if ($i != 0) {
			echo ',';
		}
		$i++;
		$queryValue = explode('=', $value);

		$pointValue = explode('|', $queryValue[1]);

		$x = $pointValue[0];//+10;
		$y = $pointValue[1];//+10;
		$location = urldecode($pointValue[2]);

		echo '
	["'.$location.'", '.$x.', '.$y.']';
	}

	echo "
]
";
}
?>
