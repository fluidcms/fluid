<?php

namespace Fluid\Models\File;

use Fluid\Fluid;

/**
 * Get Info On Uploaded File
 *
 * @package fluid
 */
class FileInfo {
	/**
	 * Get information on a file
	 * 
	 * @param   array   $file
	 * @return  array
	 */
	public static function getInfo($file) {
		if (strpos($file['type'], 'image') !== false) {
			return self::getImageInfo($file);
		}
	}
	
	/**
	 * Get information on an image
	 * 
	 * @param   array   $file
	 * @return  array
	 */
	public static function getImageInfo($file) {
		if (file_exists($file['tmp_name']) && $size = getimagesize($file['tmp_name'])) {
			return array(
				'tmp_name' => $file['tmp_name'],
				'name' => $file['name'],
				'width' => $size[0],
				'height' => $size[1],
				'type' => $size['mime'],
				'size' => filesize($file['tmp_name'])
			);
		}
		
		return;
	}
}
