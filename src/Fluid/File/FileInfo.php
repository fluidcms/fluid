<?php

namespace Fluid\File;

use Exception;

/**
 * Get Info On Uploaded File
 *
 * @package fluid
 */
class FileInfo
{
    /**
     * Get information on a file
     *
     * @param array $file
     * @return array
     */
    public static function getTmpFileInfo(array $file)
    {
        if (strpos($file['type'], 'image') !== false) {
            $info = self::getImageInfo($file['tmp_name']);
            $info['tmp_name'] = $file['tmp_name'];
            $info['name'] = $file['name'];
            return $info;
        }
    }

    /**
     * Get information on an image
     *
     * @param array $file
     * @throws Exception
     * @return array
     */
    public static function getImageInfo(array $file)
    {
        if (file_exists($file) && $size = getimagesize($file)) {
            return array(
                'name' => basename($file),
                'width' => $size[0],
                'height' => $size[1],
                'type' => $size['mime'],
                'size' => filesize($file),
                'creation' => filectime($file)
            );
        }

        throw new Exception('File not found.');
    }
}
