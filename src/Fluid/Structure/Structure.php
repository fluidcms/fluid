<?php namespace Fluid\Structure;

class Structure extends \Fluid\Model {
	protected static $file = 'structure.json';
	
	/**
	 * Find a page in the site's structure with it's URL
	 * 
	 * @param   string      $page   The url to match
	 * @return  stdClass
	 */
	public static function findPage( $page ) {
		$page = '/'.preg_replace('{index$}', '', $page);
		$matchFunction = function($needle, $haystack, $matchFunction) {
			foreach ($haystack as $item) {
				if ($item->url == $needle) return $item;
				else if (isset($item->pages) && count($item->pages)) $matchFunction($needle, $item->pages, $matchFunction);
			}
			return false;
		};		
		
		foreach(self::getAll() as $section) {
			if (is_array($section->pages) && count($section->pages)) {
				$match = $matchFunction($page, $section->pages, $matchFunction);
				if ($match) break;
			}
		}
		
		if ($match) return $match;
		else return false;
	}
}