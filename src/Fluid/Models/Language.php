<?php

namespace Fluid\Models;

use Fluid;

/**
 * Language model
 *
 * @package fluid
 */
class Language {
	/**
	 * Get languages
	 * 
	 * @return  array
	 */
	public static function getLanguages() {
		return Fluid\Fluid::getConfig('languages');
	}
}