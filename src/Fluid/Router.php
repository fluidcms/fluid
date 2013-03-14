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
		if (null === $request) {
			$request = '/';
		}
		
		$request = '/'.ltrim($request, '/');
		$structure = Models\Structure::getAll();
		
		foreach($structure as $section) {
			if (is_array($section->pages) && count($section->pages)) {
				if ($page = self::matchRequest($request, $section->pages)) {
					break;
				}
			}
		}
		
		if ($page) {
			return Page::create($page->layout, Data::get($page->url));
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
	private static function matchRequest( $request, $pages ) {
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