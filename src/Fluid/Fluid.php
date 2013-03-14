<?php

namespace Fluid;

/**
 * The fluid class
 *
 * @package fluid
 */
class Fluid {
	private static $config;
	
	private static $storage;
	private static $templates;
	private static $database;
	private static $languages;
	private static $dirs;
	
	const NOT_FOUND = '404';
	
	/**
	 * Initialize Fluid
	 * 
	 * @param   array   $config     The configuration array
	 * @return  void
	 */
	public function __construct($config) {
		self::$config = $config;
		
		// Set View Templates Directory
		if (null === View::getTemplatesDir()) {
			View::setTemplatesDir(self::getConfig('templates'));
		}
	}
	
	/**
	 * Get a configuration
	 * 
	 * @param   string  $name     The key of the config
	 * @return  void
	 */
	public static function getConfig($name) {
		switch($name) {
			case 'storage':
			case 'templates':
				if (isset(self::$config[$name])) {
					return (substr(self::$config[$name], -1) === '/' ? self::$config[$name] : self::$config[$name] . '/');
				}
				break;
			case 'database':
			case 'languages':
			case 'dirs':
				if (isset(self::$config[$name])) {
					return self::$config[$name];
				}
				break;
		}
		
		return;
	}
	
	/**
	 * Set a configuration
	 * 
	 * @param   string  $name     The key of the config
	 * @param   string  $value    The value of the config
	 * @return  void
	 */
	public static function setConfig($name, $value) {
		switch($name) {
			case 'storage':
			case 'templates':
			case 'database':
			case 'languages':
			case 'dirs':
				self::$config[$name] = $value;
				break;
		}
	}
}