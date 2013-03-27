<?php

namespace Fluid;

/**
 * Page builder class
 *
 * @package fluid
 */
class Page {
	/**
	 * Create a page.
	 * 
	 * @param   array   $page
	 * @return  void
	 */
	public static function create( $layout, $data = array() ) {		
		$view = View::create(Fluid::getConfig('layouts') . "/{$layout}.twig", $data);
		
		new StaticFile($view, 'html', true);
	}
}
