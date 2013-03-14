<?php

namespace Fluid;

use Twig_Loader_Filesystem, Twig_Environment;

/**
 * View class
 *
 * @package fluid
 */
class View {
	private static $templatesDir;
	
	/**
	 * Create a view
	 * 
	 * @param   string   $file
	 * @param   array    $data
	 * @return  string
	 */
	public static function create( $file, $data = array() ) {
		// If a valid token is provided, function will output the page Fluid data with the page content
		if (isset($_SERVER['QUERY_STRING'])) {
			parse_str($_SERVER['QUERY_STRING']);
			if (isset($fluidtoken) && Models\PageToken::validateToken($fluidtoken)) {
				return self::renderWithFields( $file, $data );
			}
		}
		
		return self::render( $file, $data );
	}
	
	/**
	 * Render a view
	 * 
	 * @param   string   $file
	 * @param   array    $data
	 * @return  string
	 */
	private static function render( $file, $data = array() ) {
		$loader = new Twig_Loader_Filesystem(self::$templatesDir);
		$twig = new Twig_Environment($loader);
		$template = $twig->loadTemplate($file);
		return $template->render($data);		
	}
	
	/**
	 * Render a view and output fields and data
	 * 
	 * @param   string   $file
	 * @param   array    $data
	 * @return  string
	 */
	private static function renderWithFields( $file, $data = array() ) {		
		$loader = new Twig_Loader_Filesystem(self::$templatesDir);
		$twig = new Twig_Environment($loader);
		
		$twig->addNodeVisitor(new Twig\FieldNodeVisitor);
				
		$template = $twig->loadTemplate($file);
		$render = $template->render($data);
		
		$render = '<script data-type="fluid-data">' . 
					json_encode(Twig\Field\FieldOutput::returnFields()) . 
					'</script>' .
					PHP_EOL . 
					$render;
		
		return $render;
	}
	
	/**
	 * Set templates directory
	 * 
	 * @param   string  $dir
	 * @return  void
	 */
	public static function setTemplatesDir($dir) {
		self::$templatesDir = $dir;
	}
	
	/**
	 * Get templates directory
	 * 
	 * @return  void
	 */
	public static function getTemplatesDir() {
		return self::$templatesDir;
	}	
}