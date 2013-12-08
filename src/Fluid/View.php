<?php

namespace Fluid;

use Twig_Loader_Filesystem;
use Twig_Environment;
use Twig_Loader_Chain;
use Twig_Loader_String;

/**
 * View class
 *
 * @package fluid
 */
class View
{
    protected static $loader;
    protected static $templatesDir;

    /**
     * Create a view
     *
     * @param string $file
     * @param array $data
     * @return string
     */
    public static function create($file, array $data = array())
    {
        return self::render($file, $data);
    }

    /**
     * Create a view from a Twig macro
     *
     * @param string $macro
     * @param string $file
     * @param array $data
     * @return string
     */
    public static function macro($macro, $file, array $data = array())
    {
        // TODO: add hook for Twig loader
        // TODO: cache twig loaders in static variables
        $loader = new Twig_Loader_Chain(array(
            new Twig_Loader_Filesystem(self::getTemplatesDir()),
            new Twig_Loader_String()
        ));

        $twig = new Twig_Environment($loader);

        $input = '{% import "' . $file . '" as macros %}{{ macros.' . $macro . '(data) }}';

        return $twig->render($input, array('data' => $data));
    }

    /**
     * Extends Twig Loader
     *
     * @param TwigLoaderInterface $loader
     */
    public static function setLoader($loader)
    {
        if (null !== $loader && !$loader instanceof TwigLoaderInterface) {
            trigger_error("Argument 1 passed to Fluid\\View::setLoader() must implement interface Fluid\\TwigLoaderInterface", E_USER_ERROR);
        }
        self::$loader = $loader;
    }

    /**
     * Initialize Twig
     *
     * @return string
     */
    public static function initTwig()
    {
        // TODO: make Twig hook event based
        // TODO: cache twig loaders in static variables

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
     * @param string $file
     * @param array $data
     * @return string
     */
    protected static function render($file, array $data = array())
    {
        // TODO: convert hook for Twig loader (same as macro method)
        // TODO: cache twig loaders in static variables

        list($loader, $twig) = static::initTwig();

        $template = $twig->loadTemplate($file);
        return $template->render($data);
    }

    /**
     * Set templates directory
     *
     * @param string $dir
     */
    public static function setTemplatesDir($dir)
    {
        self::$templatesDir = $dir;
    }

    /**
     * Get templates directory
     *
     * @return string
     */
    public static function getTemplatesDir()
    {
        if (null === self::$templatesDir) {
            self::setTemplatesDir(Config::get('twig_templates'));
        }

        return self::$templatesDir;
    }
}