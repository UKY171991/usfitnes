<?php
// Image generation script for logo
header('Content-Type: image/png');
header('Cache-Control: max-age=86400');

// Create a 200x50 image
$image = imagecreatetruecolor(200, 50);

// Enable alpha blending
imagealphablending($image, true);
imagesavealpha($image, true);

// Create colors
$transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
$blue = imagecolorallocate($image, 30, 60, 114);
$lightBlue = imagecolorallocate($image, 42, 82, 152);
$white = imagecolorallocate($image, 255, 255, 255);

// Fill the image with transparent background
imagefill($image, 0, 0, $transparent);

// Create a rounded rectangle for the logo background
imagefilledrectangle($image, 0, 0, 50, 50, $blue);

// Create a circle for the flask
imagefilledellipse($image, 25, 25, 30, 30, $white);

// Add a simple flask shape
$points = [
    20, 20, // top-left
    30, 20, // top-right
    28, 35, // bottom-right
    22, 35  // bottom-left
];
imagefilledpolygon($image, $points, 4, $blue);

// Add text "PathLab Pro"
$fontFile = realpath(__DIR__ . '/arial.ttf');
if (file_exists($fontFile)) {
    imagettftext($image, 20, 0, 60, 35, $blue, $fontFile, 'PathLab Pro');
} else {
    imagestring($image, 5, 60, 15, 'PathLab Pro', $blue);
}

// Output the image
imagepng($image);
imagedestroy($image);
?>
