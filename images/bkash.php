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
$pink = imagecolorallocate($image, 227, 24, 137); // bKash pink color
$white = imagecolorallocate($image, 255, 255, 255);

// Fill background with bKash pink
imagefilledrectangle($image, 40, 40, 160, 160, $pink);

// Draw "b" in white
$font_size = 90;
$x = 70;
$y = 140;
imagestring($image, 5, $x, $y - 40, "b", $white);

// Draw "Kash" in white
imagestring($image, 5, $x + 40, $y - 40, "Kash", $white);

// Output the image
imagepng($image);
imagedestroy($image);
?> 