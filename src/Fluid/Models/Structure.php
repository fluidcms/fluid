<?php

namespace Fluid\Models;

use Fluid\Fluid, Fluid\Database\Storage, Fluid\Models\Structure\LocalizeStructure;

/**
 * Site structure model
 *
 * @package fluid
 */
class Structure extends Storage {
	protected static $dataFile = 'structure/structure_master.json';
	
	public $pages;
	
	/**
	 * Init
	 * 
	 * @return  void
	 */
	public function __construct() {
		$this->pages = LocalizeStructure::localize(self::getAll(), LocalizeStructure::getDefaultLanguage());
		$this->localized = LocalizeStructure::localize($this->pages, Fluid::getLanguage());
	}
}