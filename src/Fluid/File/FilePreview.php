<?php

namespace Fluid\Models\File;

use Fluid\Fluid, DomainException;

/**
 * Make a preview of a file
 *
 * @package fluid
 */
class FilePreview
{
    private static $maxSize = 82;

    /**
     * Make the preview
     *
     * @param   string  $file
     * @throws  DomainException
     * @return  string
     */
    public static function make($file)
    {
        $max = self::$maxSize;
        $size = getimagesize($file);
        $width = $size[0];
        $height = $size[1];

        if ($width > $height) {
            if ($width > $max) {
                $height *= $max / $width;
                $width = $max;
            }
        } else {
            if ($height > $max) {
                $width *= $max / $height;
                $height = $max;
            }
        }
        $width = (int)round($width);
        $height = (int)round($height);

        if ($size['mime'] === "image/jpeg") {
            $img = imagecreatefromjpeg($file);
        } else if ($size['mime'] === "image/png") {
            $img = imagecreatefrompng($file);
        } else if ($size['mime'] === "image/gif") {
            $img = imagecreatefromgif($file);
        } else {
            throw new DomainException('Unknown image type: '.$size['mime']);
        }

        $newImg = imagecreatetruecolor($width, $height);

        imagealphablending($newImg, false);
        imagesavealpha($newImg, true);

        // Crop and resize image.
        imagecopyresampled($newImg, $img, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);
        imagedestroy($img);

        ob_start();
        imagepng($newImg, null, 9);
        $out = ob_get_clean();
        ob_end_clean();

        imagedestroy($newImg);

        return $out;
    }
}
