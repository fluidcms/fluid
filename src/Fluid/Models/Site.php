<?php

namespace Fluid\Models;

use Exception, Fluid\Fluid, Fluid\Database\Storage;

/**
 * Site model
 *
 * @package fluid
 */
class Site extends Storage {
	public $data;
	public $variables;
	
	/**
	 * Init
	 * 
	 * @return  void
	 */
	public function __construct() {		
		// Load page data
		try {
			$this->data = self::load('site/site_'.Fluid::getLanguage().'.json');
		} catch (Exception $e) {
			null;
		}
	}
}