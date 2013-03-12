<?php namespace Fluid\Page;

use Fluid\Structure\Structure;

class Page extends \Fluid\Model {	
	/**
	 * Get data for a page
	 * 
	 * @param		
	 * @return	
	 */
	public static function getPage( $page ) {
		return [];
	}
	
	/**
	 * Get data for a page
	 * 
	 * @param		
	 * @return	
	 */
	public static function getFields( $page ) {
		$page = Structure::findPage($page);
		GetFields::loadPage( $page );
	}
}