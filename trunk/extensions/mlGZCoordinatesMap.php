<?php
// TODO: security: verify somehow that the referer is a MediaWiki installation

header ("Content-type: image/png");

$query = explode('&', $_SERVER['QUERY_STRING']);

foreach($query as $value) {
	$queryValue = explode('=', $value);

	if (strcmp($queryValue[0], 'width') == 0) {
		$width = $queryValue[1];
		array_shift($query);
	}
	if (strcmp($queryValue[0], 'height') == 0) {
		$height = $queryValue[1];
		array_shift($query);
	}
}

$width = round($width+20);
$height = round($height+20);

$im = @imagecreatetruecolor($width, $height)
      or die("Cannot Initialize new GD image stream");

$bgColor = imagecolorallocate($im, 249, 249, 249);
imagefilledrectangle($im, 0, 0, $width-1, $height-1, $bgColor);

$text_color = imagecolorallocate($im, 0, 43, 184);

foreach($query as $value) {
	$queryValue = explode('=', $value);

	$pointValue = explode('|', $queryValue[1]);
	imageellipse($im, $pointValue[0]+10, $pointValue[1]+10, 5, 5, $text_color);
}

imagepng($im);
imagedestroy($im);
?>

