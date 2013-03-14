<?php

namespace Fluid\Models;

use Fluid\Database\Storage;

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
		$this->pages = self::getAll();
	}
	
	// !! Deprecated or to re-write 
#	/**
#	 * Get the parent of a page
#	 * 
#	 * @param   string      $page   The url to match
#	 * @return	string
#	 */
#	public function getPageParent($page) {
#		foreach($this->pages as $haystack) {
#			
#		}
#	}
#		
#	/**
#	 * Find a page in the site's structure with it's URL
#	 * 
#	 * @param   string      $page   The url to match
#	 * @return  stdClass
#	 */
#	public static function findPage( $page ) {
#		$page = '/'.preg_replace('{index$}', '', $page);
#		$matchFunction = function($needle, $haystack, $matchFunction) {
#			foreach ($haystack as $item) {
#				if ($item->url == $needle) return $item;
#				else if (isset($item->pages) && count($item->pages)) $matchFunction($needle, $item->pages, $matchFunction);
#			}
#			return false;
#		};		
#		
#		foreach(self::getAll() as $section) {
#			if (is_array($section->pages) && count($section->pages)) {
#				$match = $matchFunction($page, $section->pages, $matchFunction);
#				if ($match) break;
#			}
#		}
#		
#		if ($match) return $match;
#		else return false;
#	}
}