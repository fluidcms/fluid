<?php

namespace Fluid;

/**
 * Data class
 *
 * @package fluid
 */
class Data {
	private static $structure;
	
	/**
	 * Get data for a page
	 * 
	 * @param   string  $page
	 * @return  array
	 */
	public static function get($page) {
		$page = new Models\Page(self::$structure, $page);
		
		return array(
			'structure' => self::$structure->localized
		);
	}
	
	/**
	 * Set the site structure
	 * 
	 * @param   Models\Structure    $structure
	 * @return  void
	 */
	public static function setStructure(Models\Structure $structure) {
		self::$structure = $structure;
	}
	
	/**
	 * Get the site structure
	 * 
	 * @return  Models\Structure
	 */
	public static function getStructure() {
		return self::$structure;
	}
}