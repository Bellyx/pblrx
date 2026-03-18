<?php

function resizeImage($source, $dest, $maxWidth = 1200)
{

    list($width, $height, $type) = getimagesize($source);

    $ratio = $width / $height;

    $newWidth = $maxWidth;
    $newHeight = $maxWidth / $ratio;

    $image_p = imagecreatetruecolor($newWidth, $newHeight);

    if ($type == IMAGETYPE_JPEG) {
        $image = imagecreatefromjpeg($source);
    } elseif ($type == IMAGETYPE_PNG) {
        $image = imagecreatefrompng($source);
    }

    imagecopyresampled(
        $image_p,
        $image,
        0,
        0,
        0,
        0,
        $newWidth,
        $newHeight,
        $width,
        $height
    );

    imagejpeg($image_p, $dest, 85);
}
