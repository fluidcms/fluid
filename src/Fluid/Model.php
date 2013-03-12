<?php namespace Fluid;

class Model {
	
	public static function getAll() {
		$file = Fluid::$storage . static::$file;
		if (file_exists($file)) {
			return json_decode(file_get_contents($file));
		}
	}
	
}