<?php namespace Fluid;

use Fluid\Structure\Structure;

class Router {
	/**
	 * Route a request
	 * 
	 * @param		
	 * @return	
	 */
	public static function route( Fluid $fluid, $request ) {
		$request = '/'.ltrim($request, '/');
		$structure = Structure::getAll();
		
		foreach($structure as $section) {
			if (is_array($section->pages) && count($section->pages)) {
				if ($page = self::matchRequest($request, $section->pages)) {
					break;
				}
			}
		}
		
		if ($page) return Build::page($page);
		else return Fluid::NOT_FOUND;
	}
	
	/**
	 * Try to match a request with an array of pages
	 * 
	 * @param   string  $request	
	 * @param   array   $pages	
	 * @return  bool
	 */
	public static function matchRequest( $request, $pages ) {
		foreach($pages as $page) {
			if ($request == $page->url) {
				return $page;
			} else if (isset($page->pages) && count($page->pages)) {
				$matchPages = self::matchRequest($request, $page->pages);
				if ($matchPages) {
					return $matchPages;
				}
			}
		}
		return false;
	}
}