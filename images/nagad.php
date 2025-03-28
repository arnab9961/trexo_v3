<?php
// Set content type to PNG image
header('Content-Type: image/png');

// Create image
$width = 200;
$height = 200;
$image = imagecreatetruecolor($width, $height);

// Make background transparent
imagealphablending($image, false);
$transparency = imagecolorallocatealpha($image, 0, 0, 0, 127);
imagefill($image, 0, 0, $transparency);
imagesavealpha($image, true);

// Colors
$orange = imagecolorallocate($image, 244, 94, 55); // Nagad orange color
$white = imagecolorallocate($image, 255, 255, 255);

// Fill background with Nagad orange
imagefilledrectangle($image, 40, 40, 160, 160, $orange);

// Draw "Nagad" in white
$font_size = 90;
$x = 50;
$y = 140;
imagestring($image, 5, $x, $y - 40, "NAGAD", $white);

// Output the image
imagepng($image);
imagedestroy($image);
?> 