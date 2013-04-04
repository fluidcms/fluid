<?php

namespace Fluid;

use Twig_Loader_Filesystem, Twig_Environment;

/**
 * View class
 *
 * @package fluid
 */
class View {
	protected static $loader;
	protected static $templatesDir;
	
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
	 * Extends Twig Loader
	 * 
	 * @param   TwigLoaderInterface $loader
	 * @return  void
	 */
	public static function setLoader($loader) {
		if (null !== $loader && !$loader instanceof TwigLoaderInterface) {
			trigger_error("Argument 1 passed to Fluid\View::setLoader() must implement interface Fluid\TwigLoaderInterface", E_USER_ERROR);
		}
		self::$loader = $loader;
	}
		
	/**
	 * Initialize Twig
	 * 
	 * @param   string   $file
	 * @param   array    $data
	 * @return  string
	 */
	public static function initTwig() {
		if (null !== self::$loader) {
			return call_user_func(array(self::$loader, 'loader'));
		}
		
		return array(
			$loader = new Twig_Loader_Filesystem(self::$templatesDir),
			new Twig_Environment($loader)
		);
	}
	
	/**
	 * Render a view
	 * 
	 * @param   string   $file
	 * @param   array    $data
	 * @return  string
	 */
	protected static function render( $file, $data = array() ) {
		list($loader, $twig) = static::initTwig();

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
	protected static function renderWithFields( $file, $data = array() ) {		
		list($loader, $twig) = static::initTwig();
		
		$twig->addNodeVisitor(new Twig\FieldNodeVisitor);
				
		$template = $twig->loadTemplate($file);
		$render = $template->render($data);
		
		$render = 	
					'<script data-type="fluid-language">' . json_encode(Fluid::getLanguage()) . '</script>' .
					PHP_EOL . 
					'<script data-type="fluid-page">' . json_encode(Data::getPage()) . '</script>' .
					PHP_EOL . 
					'<script data-type="fluid-data">' . json_encode(Twig\Field\FieldOutput::returnFields()) . '</script>' .
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