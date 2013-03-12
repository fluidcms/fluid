<?php namespace Fluid;

use Fluid\Structure\Structure, Fluid\Page\Page;

class Admin {
	/**
	 * Route an admin request
	 * 
	 * @param		
	 * @return	
	 */
	public static function getResponse( Fluid $fluid, $request ) {
		// Public files
		if (!empty($request)) {
			$file = __DIR__.'/Public/'.trim($request, ' ./');
			if (file_exists($file)) {
			    return new StaticFile($file);
			}
		}
		
		// Pages Routing
		if (strpos($request, 'page/') === 0) {
			Page::getFields(substr($request, 5, -5));
			die();
		}
		
		// Other files
		switch($request) {
			case '': return View::create(__DIR__."/Templates/", 'master.twig', [ // !! No PHP 5.4 Arrays you fool
				'navigation'=>[
					['name'=>'Structure', 'class'=>'structure'],
					['name'=>'Components', 'class'=>'components'],
/* 					['name'=>'Files', 'class'=>'files'], */
					['name'=>'Internationalization', 'class'=>'intl']
/* 					['name'=>'Changes', 'class'=>'diff'] */
				],
				'site_url' => Fluid::$urls['staging']
			]);
			case 'structure.json': return json_encode(Structure::getAll());
			default: return Fluid::NOT_FOUND;
		}
	}

}
