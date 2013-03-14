<?php

namespace Fluid\Database;

use Fluid\Fluid;

/**
 * File storage helper class
 *
 * @package fluid
 */
class Storage {
	
	public static function getAll() {
		$file = Fluid::getConfig('storage') .  static::$file;
		if (file_exists($file)) {
			return json_decode(file_get_contents($file));
		}
	}
	
}