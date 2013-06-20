<?php

namespace Fluid;

/**
 * Page builder class
 *
 * @package fluid
 */
class PageMaker
{
    /**
     * Create a page.
     *
     * @param   array   $page
     * @return  string
     */
    public static function create($layout, $data = array())
    {
        $view = View::create(Fluid::getConfig('layouts') . "/{$layout}.twig", $data);

        new StaticFile($view, 'html', true);
    }
}
