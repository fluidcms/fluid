<?php

namespace Fluid\Admin;

use Fluid;

/**
 * Route requests to admin interface.
 *
 * @package fluid
 */
class Router {
	/**
	 * Route an admin request
	 * 
	 * @param   string  $request
	 * @return  mixed
	 */
	public static function route($request) {
		Fluid\View::setTemplatesDir(__DIR__."/Templates/");
		
		// Public files
		if (!empty($request)) {
			$file = __DIR__.'/Public/'.trim($request, ' ./');
			if (file_exists($file)) {
			    return new Fluid\StaticFile($file);
			}
		}
				
		// Other files
		switch($request) {
			// Index
			case '':
				return Fluid\View::create('master.twig', array(
					'navigation' => array(
						array('name'=>'Structure', 'class'=>'structure'),
						array('name'=>'Components', 'class'=>'components'),
						array('name'=>'Internationalization', 'class'=>'intl')
					),
					'site_url' => 'http://sinergi-fluid.zulu/' // !! Fluid\Fluid::$urls['staging']
				));
			
			// Test
			case 'test':
				return Fluid\View::create('test.twig');
			
			// Structure
			case 'structure.json':
				return json_encode(Fluid\Models\Structure::getAll());
			
			// Page
			case 'page.json':
				return json_encode(Fluid\Models\Page::getAllData($_POST['content'], $_POST['url']));
			
			// Languages
			case 'languages.json':
				return json_encode(Fluid\Models\Language::getLanguages());
			
			// Page Token
			case 'pagetoken.json':
				return json_encode(array('token'=>Fluid\Models\PageToken::getToken()));
			
			// Not found
			default:
				return Fluid::NOT_FOUND;
		}
	}
}