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
		$site = new Models\Site();
		$page = new Models\Page(self::$structure, $page);
		
		// Make parents tree
		$parentTree = null;
		$current = $page;
		$parents = array();
		
		while($current->parent instanceof Models\Page) {
			$current = $current->parent;
			$parents[] = $current->data;
		}
		
		foreach($parents as $parent) {
			if (!isset($parentTree)) {
				$parentTree = $parent;
				$last = &$parentTree;
			} else {
				$last['parent'] = $parent;
				$last = &$last['parent'];
			}
		}
				
		return array(
			'site' => $site->data,
			'structure' => self::$structure->localized,
			'parents' => $parents,
			'parent' => $parentTree,
			'page' => $page->data
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