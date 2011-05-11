<?php
echo "test1.jpg:<br />\n";
$exif = exif_read_data('colloseum.jpg', 'IFD0');
echo $exif===false ? "No header data found.<br />\n" : "Image contains headers<br />\n";

$exif = exif_read_data('colloseum.jpg', 0, true);
echo "test2.jpg:<br />\n";
foreach ($exif as $key => $section) {
    foreach ($section as $name => $val) {
        echo utf8_encode("$key.$name: $val<br />\n");
		if(is_array($val)){
			foreach($val as $k=>$v){
				echo $k . ' => ' . $v . ' | ';
			}
		}
    }
}


function exifToNumber($value, $format) {
	$spos = strpos($value, '/');
	if ($spos === false) {
		return sprintf($format, $value);
	} else {
		list($base,$divider) = split("/", $value, 2);
		if ($divider == 0) 
			return sprintf($format, 0);
		else
			return sprintf($format, ($base / $divider));
	}
}

function exifToCoordinate($reference, $coordinate) {
	if ($reference == 'S' || $reference == 'W')
		$prefix = '-';
	else
		$prefix = '';
		
	return $prefix . sprintf('%.6F', exifToNumber($coordinate[0], '%.6F') +
		(((exifToNumber($coordinate[1], '%.6F') * 60) +	
		(exifToNumber($coordinate[2], '%.6F'))) / 3600));
}

function getCoordinates($filename) {
	if (extension_loaded('exif')) {
		$exif = exif_read_data($filename, 'EXIF');
		
		if (isset($exif['GPSLatitudeRef']) && isset($exif['GPSLatitude']) && 
			isset($exif['GPSLongitudeRef']) && isset($exif['GPSLongitude'])) {
			return array (
				exifToCoordinate($exif['GPSLatitudeRef'], $exif['GPSLatitude']), 
				exifToCoordinate($exif['GPSLongitudeRef'], $exif['GPSLongitude'])
			);
		}
	}
}

function coordinate2DMS($coordinate, $pos, $neg) {
	$sign = $coordinate >= 0 ? $pos : $neg;
	
	$coordinate = abs($coordinate);
	$degree = intval($coordinate);
	$coordinate = ($coordinate - $degree) * 60;
	$minute = intval($coordinate);
	$second = ($coordinate - $minute) * 60;
	
	return sprintf("%s %d&#xB0; %02d&#x2032; %05.2f&#x2033;", $sign, $degree, $minute, $second);
}


if ($c = getCoordinates('colloseum.jpg')) {
		echo 'Latitude: ' . $latitude = $c[0];
		echo '<br/>';
		echo 'Longitude: ' . $longitude = $c[1];
		
		echo '<br/>';
		echo coordinate2DMS($c[0], 'N', 'E'); // I have no clue if this what I'm supose to do...
		echo '<br/>';
		echo coordinate2DMS($c[1], 'E', 'N'); // I have no clue if this what I'm supose to do...
}
?>