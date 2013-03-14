<?php

namespace Fluid\Database;

use Fluid\Fluid;

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
		$file = Fluid::getConfig('storage') .  static::$dataFile;
		if (file_exists($file)) {
			return json_decode(file_get_contents($file));
		}
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