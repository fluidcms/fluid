<?php namespace Fluid;

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
			
			// Structure
			case 'structure.json': return json_encode(Structure\Structure::getAll());
			
			// Page
			case 'page.json': return json_encode(Page\Page::getAllData($_POST['content'], $_POST['url']));
			
			// Page Token
			case 'pagetoken.json': return json_encode(array('token'=>Page\Token::getToken()));
			
			default: return Fluid::NOT_FOUND;
		}
	}
}
