<?php

namespace Fluid;

use Twig_Loader_Filesystem,
    Twig_Environment,
    Fluid\Token\Token;

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
     * @param   string   $file
     * @param   array    $data
     * @return  string
     */
    public static function create($file, $data = array())
    {
        return self::render($file, $data);
    }

    /**
     * Extends Twig Loader
     *
     * @param   TwigLoaderInterface $loader
     * @return  void
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
     * @param   string   $file
     * @param   array    $data
     * @return  string
     */
    public static function initTwig()
    {
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
    protected static function render($file, $data = array())
    {
        list($loader, $twig) = static::initTwig();

        $template = $twig->loadTemplate($file);
        return $template->render($data);
    }

    /**
     * Set templates directory
     *
     * @param   string  $dir
     * @return  void
     */
    public static function setTemplatesDir($dir)
    {
        self::$templatesDir = $dir;
    }

    /**
     * Get templates directory
     *
     * @return  void
     */
    public static function getTemplatesDir()
    {
        return self::$templatesDir;
    }
}