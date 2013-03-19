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
	private $localized = array();
	
	public $pages;
	
	/**
	 * Init
	 * 
	 * @return  void
	 */
	public function __construct() {
		$this->pages = LocalizeStructure::localize(self::getAll(), LocalizeStructure::getDefaultLanguage());
	}
	
	/**
	 * Localize the structure
	 * 
	 * @param   string  $language
	 * @return  array
	 */
	public function getLocalized($language = null) {
		if (null === $language) {
			$language = Fluid::getLanguage();
		}
		
		if (!isset($this->localized[$language])) {
			return $this->localized[$language] = LocalizeStructure::localize($this->pages, $language);
		} else {
			return $this->localized[$language];
		}
	}
}