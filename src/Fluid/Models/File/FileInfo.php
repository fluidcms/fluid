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
	public static function getTmpFileInfo($file) {
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
	 * @param   array   $file
	 * @return  array
	 */
	public static function getImageInfo($file) {
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
		
		return;
	}
}
