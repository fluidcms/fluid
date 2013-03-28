<?php

namespace Fluid\Models\File;

use Fluid\Fluid;

/**
 * Make a preview of a file
 *
 * @package fluid
 */
class FilePreview {
	private static $maxSize = 82;
	
	/**
	 * Make the preview
	 * 
	 * @param   string  $file
	 * @return  string
	 */
	public static function make($file) {
		$max = self::$maxSize;
		$size = getimagesize($file);
		$width = $size[0];
		$height = $size[1];
		
		if ($width > $height) {
		    if ($width > $max) {
		    	$height *= $max/$width;
		    	$width = $max;
		    }
		} else {
		    if ($height > $max) {
		    	$width *= $max/$height;
		    	$height = $max;
		    }
		}
		$width = (int) round($width);
		$height = (int) round($height);
				
		if ($size['mime'] === "image/jpeg") $img = imagecreatefromjpeg($file);
		else if ($size['mime'] === "image/png") $img = imagecreatefrompng($file);
		else if ($size['mime'] === "image/gif") $img = imagecreatefromgif($file);
	
		$newImg = imagecreatetruecolor($width, $height);

		// Fill background
		$white = imagecolorallocate($newImg, 255, 255, 255);
		imagefill($newImg, 0, 0, $white);
	
		// Crop and resize image. 
		imagecopyresampled($newImg, $img, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);
		imagedestroy($img);
		
		ob_start();
		imagejpeg($newImg, null, 100);
		$out = ob_get_clean();
		ob_end_clean();
		
		imagedestroy($newImg);
		
		return $out;
	}
}
