<?php

namespace Fluid;

/**
 * Autoloads Fluid classes
 *
 * @package fluid
 */
class Autoloader
{
    /**
     * Register the autoloader
     *
     * @return  void
     */
    public static function register()
    {
        spl_autoload_register(array(new self, 'autoload'));
    }

    /**
     * Autoloader
     *
     * @param   string
     * @return  mixed
     */
    public static function autoload($class)
    {
        if (0 !== strpos($class, 'Fluid\\')) {
            return null;
        } else if (file_exists($file = __DIR__ . '/' . str_replace('\\', '/', preg_replace('{^Fluid\\\}', '', $class)) . '.php')) {
            require_once $file;
            return true;
        }
    }
}