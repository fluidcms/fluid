<?php

namespace Fluid;

/**
 * Route requests to pages.
 *
 * @package fluid
 */
class PageRouter
{
    private static $request;

    /**
     * Route a request
     *
     * @param   string  $request
     * @return  mixed
     */
    public static function route($request = null)
    {
        if (null === $request && isset($_SERVER['REQUEST_URI'])) {
            $request = $_SERVER['REQUEST_URI'];
        }

        $request = '/' . ltrim($request, '/');

        $map = new Map;
        $page = self::matchRequest($request, $map->getPages());

        if (isset($page) && false !== $page) {
            Data::setMap($map);
            return PageMaker::create(Data::get($page['id']));
        }

        return Fluid::NOT_FOUND;
    }

    /**
     * Try to match a request with an array of pages
     *
     * @param   string  $request
     * @param   array   $pages
     * @param   string  $parent
     * @return  bool
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