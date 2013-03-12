<?php namespace Fluid;

class Fluid {
	public static $storage;
	public static $templates;
	public static $urls;
	
	const NOT_FOUND = '404';
	
	/**
	 * 
	 * 
	 * @param   array   $config     The configuration array
	 * @return  void
	 */
	public function __construct( $config ) {
		self::$storage = (substr($config['storage'], -1) === '/' ? $config['storage'] : $config['storage'] . '/');
		self::$templates = (substr($config['templates'], -1) === '/' ? $config['templates'] : $config['templates'] . '/');
		self::$urls = array();
		self::$urls['staging'] = (isset($config['urls']['staging']) ? $config['urls']['staging'] : '');
		self::$urls['production'] = (isset($config['urls']['production']) ? $config['urls']['production'] : '');
	}
}