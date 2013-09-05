<?php

namespace Fluid;

use Fluid\Layout\Layout;

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
     * @param   array   $data
     * @return  string
     */
    public static function create($page, $data = array())
    {
        $layout = Layout::get($page['layout']);

        $view = View::create(Fluid::getConfig('layouts') . "/{$page['layout']}/".$layout->getFile(), $data);

        new StaticFile($view, 'html', true);
    }
}
