<?php

namespace Fluid;

use Fluid\Layout\Layout;

/**
 * Page builder class
 * // TODO find a name that doesnt sound like the software
 * @package fluid
 */
class PageMaker
{
    /**
     * Create a page.
     *
     * @param array $page
     * @param array $data
     * @return string
     */
    public static function create(array $page, array $data = array())
    {
        $layout = Layout::get($page['layout']);

        $view = View::create(Config::get('layouts') . "/{$page['layout']}/" . $layout->getFile(), $data);

        new StaticFile($view, 'html', true);
    }
}
