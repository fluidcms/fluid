<?php

namespace Fluid\Database;

use Exception, Fluid\Fluid;

/**
 * File storage helper class
 *
 * @package fluid
 */
class Storage {	
	/**
	 * Get all data from storage
	 * 
	 * @return  array
	 */
	public static function getAll() {
		return self::load(static::$dataFile);
	}
	
	/**
	 * Get all data from storage
	 * 
	 * @return  array
	 */
	public static function load($file = null) {
		if (null === $file) {
			$file = static::$dataFile;
		}
		
		$file = Fluid::getConfig('storage') .  $file;
		
		if (file_exists($file)) {
			return json_decode(file_get_contents($file), true);
		} else {
			throw new Exception("Failed to load data: File {$file} does not exists", E_USER_WARNING);
		}
	}
		
	/**
	 * Save data to storage
	 * 
	 * @return  array
	 */
	public static function save($content, $file = null) {
		if (null === $file) {
			$file = static::$dataFile;
		}
		
		$file = Fluid::getConfig('storage') .  $file;
		
		file_put_contents($file, $content);
	}
	
	/**
	 * Set the data file
	 * 
	 * @param   string
	 * @return  void
	 */
	public static function setDataFile($file) {
		self::$dataFile = $file;
	}
	
	/**
	 * Get the data file
	 * 
	 * @return  string
	 */
	public static function getDataFile() {
		return self::$dataFile;
	}
}