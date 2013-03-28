<?php

namespace Fluid\Models;

use Exception, Fluid\Fluid, Fluid\Models\File\FileInfo;

/**
 * File model
 *
 * @package fluid
 */
class File {		
	/**
	 * Init
	 * 
	 * @return  void
	 */
	public function __construct() {		
		
	}
		
	/**
	 * Save file
	 * 
	 * @return  void
	 */
	public static function save() {
		if ($file = self::upload()) {
			
			// !! Save file
			
			return $file;
		}
	}
	
	/**
	 * Upload file
	 * 
	 * @return  void
	 */
	public static function upload() {
		foreach ($_FILES as $file) {
			if (!$file['error'] && isset($_POST['id']) && strlen($_POST['id']) === 8 && self::idIsUnique($_POST['id'])) {
				$file = FileInfo::getInfo($file);
				if ($file['size'] <= 2097152) {
					rename($file["tmp_name"], Fluid::getConfig('storage').'files/'.$_POST['id'].'_'.$file['name']);
					unset($file["tmp_name"]);
					return array_merge(array('id' => $_POST['id']), $file);
				}
			}
		}
		return;
	}
	
	/**
	 * Check if file uploaded id is unique
	 * 
	 * @param   string  $id
	 * @return	bool
	 */
	public static function idIsUnique($id) {
		$dir = scandir(Fluid::getConfig('storage').'files/');
		foreach($dir as $file) {
			if (substr($file, 0, 8) === $id) {
				return false;
			}
		}
		
		return true;
	}
}