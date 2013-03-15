<?php 

namespace Fluid;

/**
 * Route requests to pages.
 *
 * @package fluid
 */
class Router {
	/**
	 * Route a request
	 * 
	 * @param   string  $request
	 * @return  mixed
	 */
	public static function route( $request = null ) {		
		$request = '/'.ltrim($request, '/');
		
		if (!$structure = Data::getStructure()) {
			Data::setStructure($structure = new Models\Structure());
		}
		
		$page = self::matchRequest($request, $structure->pages);
				
		if (isset($page) && false !== $page) {
			return Page::create($page['layout'], Data::get($page['page']));
		} else {
			return Fluid::NOT_FOUND;
		}
	}
	
	/**
	 * Try to match a request with an array of pages
	 * 
	 * @param   string  $request	
	 * @param   array   $pages
	 * @return  bool
	 */
	private static function matchRequest( $request, $pages, $parent = '' ) {
		foreach($pages as $page) {
			if (isset($page['url']) && $request == $page['url']) {
				$page['page'] = trim($parent . '/' . $page['page'], '/');
				return $page;
			} else if (isset($page['pages']) && is_array($page['pages'])) {
				$matchPages = self::matchRequest($request, $page['pages'], trim($parent . '/' . $page['page'], '/'));
				if ($matchPages) {
					return $matchPages;
				}
			}
		}
		return false;
	}
}