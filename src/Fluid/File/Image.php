<?php

namespace Fluid\File;

use Fluid\Token\Token,
    DomainException;

class Image
{
    /**
     * Format an image
     *
     * @param   string  $id
     * @param   array   $format
     * @return  array
     */
    public static function format($id, $format)
    {
        $formats = self::parseFormatArray($format);

        $file = new File($id);
        $path = $file->getPath();
        $info = FileInfo::getImageInfo($path);

        if (isset($info['type']) && !empty($info['type'])) {
            $info['type'] = preg_replace('/^image\//', '', trim(strtolower($info['type'])));
        }

        $retval = array();
        foreach($formats as $format) {
            if (
                (isset($format['width']) && $info['width'] !== $format['width']) ||
                (isset($format['height']) && $info['height'] !== $format['height']) ||
                (isset($format['type']) && $info['type'] !== $format['type'])
            ) {
                $retval[$format['name']] = self::formatImage($info, $path, $format);
            } else {
                $retval[$format['name']] = self::getFormatArray($info, $path, $format);
            }
        }

        return $retval;
    }

    /**
     * Format image info for output
     *
     * @param   array   $info
     * @param   string  $path
     * @param   array   $format
     * @return  array
     */
    private static function getFormatArray($info, $path, $format)
    {
        return array(
            'id' => '',
            'format' => $info["type"],
            'width' => $info["width"],
            'height' => $info["height"],
            'path' => $path
        );
    }

    /**
     * Do the actual formatting
     *
     * @param   array   $info
     * @param   string  $path
     * @param   array   $format
     * @throws  DomainException
     * @return  array
     */
    private static function formatImage($info, $path, $format)
    {
        $uniqueId = Token::generate(8);

        // Determine type
        $type = 'png';
        if (isset($format['format']) && !empty($format['format'])) {
            $type = $format['format'];
        } else if (isset($info['type']) && !empty($info['type'])) {
            $type = $info['type'];
        }

        if ($type !== 'png' && $type !== 'jpg' && $type !== 'jpeg' && $type !== 'gif') {
            $type = 'png';
        }

        // Determine new path
        $dir = dirname($path);
        mkdir("{$dir}/{$uniqueId}");
        $newPath = "{$dir}/{$uniqueId}/{$info['name']}";

        // Determine new width and height
        if (isset($format['width']) && is_int($format['width'])) {
            $width = $format['width'];
        }

        if (isset($format['height']) && is_int($format['height'])) {
            $height = $format['height'];
        }

        if (!isset($width) && isset($height) && $height !== (int)$info['height']) {
            $width = round($height / $info['height'] * $info['width']);
        } else if (!isset($width)) {
            $width = (int)$info['width'];
        }

        if (!isset($height) && isset($width) && $width !== (int)$info['width']) {
            $height = round($width / $info['width'] * $info['height']);
        } else if (!isset($height)) {
            $height = (int)$info['height'];
        }

        // Create the image
        if ($info['type'] === "jpeg" || $info['type'] === "jpg") {
            $img = imagecreatefromjpeg($path);
        } else if ($info['type'] === "png") {
            $img = imagecreatefrompng($path);
        } else if ($info['type'] === "gif") {
            $img = imagecreatefromgif($path);
        } else {
            throw new DomainException('Unknown image type: '.$info['type']);
        }

        $newImg = imagecreatetruecolor($width, $height);

        if ($type === 'png' || $type === 'gif') {
            imagealphablending($newImg, false);

            $transparent = imagecolorallocatealpha($newImg, 0, 0, 0, 127);
            imagefill($newImg, 0, 0, $transparent);

            imagesavealpha($newImg, true);

        } else if ($type === 'jpg' || $type === 'jpeg') {
            $white = imagecolorallocate($newImg, 255, 255, 255);
            imagefill($newImg, 0, 0, $white);
        }

        // Copy image
        if ($width == (int)$info['width'] && $height == (int)$info['height']) {
            imagecopy($newImg, $img, 0, 0, 0, 0, $width, $height);
        }

        // Resize and crop image
        else {
            // Get resize ratio and cropping offset.
            $widthRatio = $info['width'] / $width;
            $heightRatio = $info['height'] / $height;

            // Resize
            if ($widthRatio <= $heightRatio) { // Invert lower than with greater than for none-croping resizing TODO: add option for non-croping resizing
                $ratio = $widthRatio;

                $newWidth = $info['width'] / $ratio;
                $newHeight = $info['height'] / $ratio;

                $offsetY = ($height - $newHeight) / 2;
                $offsetX = 0;
            }
            else {
                $ratio = $heightRatio;

                $newWidth = $info['width'] / $ratio;
                $newHeight = $info['height'] / $ratio;

                $offsetY = 0;
                $offsetX = ($width - $newWidth) / 2;
            }

            imagecopyresampled($newImg, $img, $offsetX, $offsetY, 0, 0, $newWidth, $newHeight, $info['width'], $info['height']);
        }

        imagedestroy($img);

        switch($type) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($newImg, $newPath, 80);
                break;
            case 'png':
                imagepng($newImg, $newPath, 9);
                break;
            case 'gif':
                imagegif($newImg, $newPath);
                break;
        }

        return array(
            'id' => $uniqueId,
            'format' => $type,
            'width' => $width,
            'height' => $height,
            'path' => $newPath
        );
    }

    /**
     * Prepare the format array
     *
     * @param   array   $format
     * @return  array
     */
    private static function parseFormatArray($format)
    {
        $formats = array();

        if (isset($format['formats']) && is_array($format['formats'])) {
            $formats = $format['formats'];
            unset($format['formats']);
        }

        $formats = array_merge(array($format), $formats);

        $retval = array();
        foreach($formats as $name => $item) {
            $format = array();

            if ($name === 0) {
                $format['name'] = null;
            } else {
                $format['name'] = (string)$name;
            }

            if (isset($item['width'])) {
                $format['width'] = (int)$item['width'];
            }

            if (isset($item['height'])) {
                $format['height'] = (int)$item['height'];
            }

            if (isset($item['format'])) {
                $format['type'] = (string)$item['format'];
            }

            $retval[] = $format;
        }

        return $retval;
    }
}