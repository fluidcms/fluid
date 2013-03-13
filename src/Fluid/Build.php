<?php namespace Fluid;

use Fluid\Site\Site, Fluid\Structure\Structure, Fluid\Page\Page;

class Build {
	/**
	 * Build a page.
	 * 
	 * @param   array   $page
	 * @return  void
	 */
	public static function page( $page ) {		
		$data = array(
			'site' => Site::getAll(),
			'sections' => Structure::getAll(),
			'page' => Page::getPage( $page )
		);
		
		$layout = "default";
		
		$view = View::create(Fluid::$templates, 'layouts/default.twig', $data, $page);
		
		echo $view;
		exit;
	}
	
}