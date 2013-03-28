<?php

namespace Fluid\Models;

use Exception, Fluid\Fluid, Fluid\Models\File\FileInfo, Fluid\Models\File\FilePreview;

/**
 * File model
 *
 * @package fluid
 */
class File {		
	/**
	 * Get all files
	 * 
	 * @return  void
	 */
	public static function getFiles() {		
		$output = array();
		$dir = scandir(Fluid::getConfig('storage').'files/');
		foreach($dir as $file) {
			$id = substr($file, 0, 8);
			if (strlen($id) === 8 && ctype_alnum($id)) {
				if ($file = FileInfo::getImageInfo(Fluid::getConfig('storage').'files/'.$file)) {
					$file["name"] = substr($file["name"], 9);
					$output[] = array_merge(array("id" => $id, 'src' => "/fluidcms/files/preview/{$id}/{$file['name']}"), $file);
				}
			}
		}
		return $output;
	}
		
	/**
	 * Save file
	 * 
	 * @return  void
	 */
	public static function save() {
		if ($file = self::upload()) {
			return $file;
		}
		return;
	}
		
	/**
	 * Delete file
	 * 
	 * @param   string  $id
	 * @return  void
	 */
	public static function delete($id) {
		$dir = scandir(Fluid::getConfig('storage').'files/');
		foreach($dir as $file) {
			if (substr($file, 0, 8) === $id) {
				unlink(Fluid::getConfig('storage').'files/'.$file);
				return true;
			}
		}
		
		return false;
	}
	
	
	/**
	 * Make an image preview
	 * 
	 * @param   string  $file
	 * @return  string
	 */
	public static function makePreview($file) {
		return FilePreview::make($file);
	}
	
	/**
	 * Upload file
	 * 
	 * @return  void
	 */
	public static function upload() {
		foreach ($_FILES as $file) {
			if (!$file['error'] && isset($_POST['id']) && strlen($_POST['id']) === 8 && self::idIsUnique($_POST['id'])) {
				$file = FileInfo::getTmpFileInfo($file);
				if ($file['size'] <= 2097152) {
					rename($file["tmp_name"], Fluid::getConfig('storage').'files/'.$_POST['id'].'_'.$file['name']);
					unset($file["tmp_name"]);
					return array_merge(array('id' => $_POST['id'], 'src' => "/fluidcms/files/preview/{$_POST['id']}/{$file['name']}"), $file);
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