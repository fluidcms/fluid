<?php

namespace Fluid\Models;

use Fluid\Database\Storage;

/**
 * Page model
 *
 * @package fluid
 */
class Page extends Storage {
	public $parent;
	
	/**
	 * Init
	 * 
	 * @param   Structure   $structure  The site's structure
	 * @param   string      $name       The unique identifier of a page (i.e. contact/form)
	 * @return  void
	 */
	public function __construct(Structure $structure, $page) {
		// Check if page has parents
		$parent = explode('/', strrev($page), 2);
		if (isset($parent[1])) {
			$parent = strrev($parent[1]);
			$this->parent = new Page($structure, $parent);
		}
		
		$this->dataFile = $page;
		
		var_dump($this->dataFile);
	}
		
	/**
	 * Check if page has parent page
	 * 
	 * @return  bool
	 */
	public function hasParent() {
		return (isset($this->parent) ? true : false);
	}
}