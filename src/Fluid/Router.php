<?php

namespace Fluid;
use Fluid\Requests\HTTP;

/**
 * Route requests to pages.
 *
 * @package fluid
 */
class Router
{
    /**
     * Route a request
     *
     * @param   string  $request
     * @return  mixed
     */
    public static function route($request = null)
    {
        // Route requests
        if (stripos($request, '/fluidcms/') === 0) {
            if (HTTP::route($request)) {
                return true;
            }
        }
        // Route pages
        else {
            if (null === $request && isset($_SERVER['REQUEST_URI'])) {
                $request = $_SERVER['REQUEST_URI'];
            }

            $request = '/' . ltrim($request, '/');

            $map = new Map\Map;
            $page = self::matchRequest($request, $map->getPages());

            if (isset($page) && false !== $page) {
                Data::setMap($map);
                return PageMaker::create($page, Data::get($page['id']));
            }
        }

        return Fluid::NOT_FOUND;
    }

    /**
     * Try to match a request with an array of pages
     *
     * @param   string  $request
     * @param   array   $pages
     * @param   string  $parent
     * @return  array
     */
    private static function matchRequest($request, $pages, $parent = '')
    {
        foreach ($pages as $page) {
            if (isset($page['url']) && $request == $page['url']) {
                $page['page'] = trim($parent . '/' . $page['page'], '/');
                return $page;
            } else if (isset($page['pages']) && is_array($page['pages'])) {
                $matchPages = self::matchRequest($request, $page['pages'], trim($parent . '/' . $page['page'], '/'));
                if ($matchPages) {
                    return $matchPages;
                }
            }
        }
        return false;
    }
}