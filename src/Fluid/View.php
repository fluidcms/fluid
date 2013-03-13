<?php namespace Fluid;

class View {
	/**
	 * Create a view
	 * 
	 * @param   string   $dir
	 * @param   string   $file
	 * @param   array    $data
	 * @param   array    $page
	 * @return  string
	 */
	public static function create( $dir, $file, $data = array(), $page = array() ) {
		if (isset($_SERVER['QUERY_STRING'])) {
			parse_str($_SERVER['QUERY_STRING']);
			if (isset($fluidtoken) && Page\Token::validateToken($fluidtoken)) {
				return self::renderWithFields( $dir, $file, $data, $page );
			}
		}
		
		return self::render( $dir, $file, $data );
	}
	
	/**
	 * Render a view
	 * 
	 * @param   string   $dir
	 * @param   string   $file
	 * @param   array    $data
	 * @return  string
	 */
	public static function render( $dir, $file, $data = array() ) {
		$loader = new \Twig_Loader_Filesystem($dir);
		$twig = new \Twig_Environment($loader);
		$template = $twig->loadTemplate($file);
		return $template->render($data);		
	}
	
	/**
	 * Render a view and output fields and data
	 * 
	 * @param   string   $dir
	 * @param   string   $file
	 * @param   array    $data
	 * @param   array    $page
	 * @return  string
	 */
	public static function renderWithFields( $dir, $file, $data = array(), $page = array() ) {		
		$loader = new \Twig_Loader_Filesystem($dir);
		$twig = new \Twig_Environment($loader);
		
		$twig->addNodeVisitor(new Twig\FieldNodeVisitor);
				
		$template = $twig->loadTemplate($file);
		$render = $template->render($data);
		
		echo '<script data-type="fluid-data">' . 
				json_encode(
					array_merge(
						array($page),
						Twig\Field\FieldOutput::returnFields()
					)
				) . 
				'</script>' .
				PHP_EOL;
		
		return $render;
	}
}