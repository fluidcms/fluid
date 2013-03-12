<?php namespace Fluid;

use Fluid\Site\Site, Fluid\Structure\Structure, Fluid\Page\Page;

class Build {
	/**
	 * 
	 * 
	 * @param		
	 * @return	
	 */
	public static function page( $page ) {
		$site = Site::getAll();
		$sections = Structure::getAll();
		$page = Page::getPage( $page );
		
		$layout = "default";
		
		$view = View::create(Fluid::$templates, 'layouts/default.twig', ['site'=>$site, 'sections'=>$sections, 'page'=>$page]);
		
		echo $view;
		
		die();
	}
	
}