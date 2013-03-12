<?php namespace Fluid;

class View {
	public static function create( $dir, $file, $data = array() ) {
		if (isset($_SERVER['QUERY_STRING'])) {
			parse_str($_SERVER['QUERY_STRING']);
			if (isset($fluidtoken)/* && // !! validatetoken */) {
				return self::getFields( $dir, $file, $data );
			}
		}
		
		return self::render( $dir, $file, $data );
	}

	public static function render( $dir, $file, $data = array() ) {
		$loader = new \Twig_Loader_Filesystem($dir);
		$twig = new \Twig_Environment($loader);
		$template = $twig->loadTemplate($file);
		return $template->render($data);		
	}

	public static function getFields( $dir, $file, $data = array() ) {
		
		$loader = new \Twig_Loader_Filesystem($dir);
		$twig = new \Twig_Environment($loader);
		
		$twig->addNodeVisitor(new Twig\FieldNodeVisitor);
				
		$template = $twig->loadTemplate($file);
		$render = $template->render($data);
		
		Twig\Field\FieldOutput::returnFields();
		
		return $render;
	}
}