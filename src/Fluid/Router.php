<?php
namespace Fluid;

use Fluid\Map\Map;
use Fluid\Layout\Layout;
use Fluid\Requests\HTTP;

/**
 * Route requests to pages.
 *
 * @package fluid
 */
class Router
{
    /** @var \Fluid\Fluid $fluid */
    private $fluid;

    /** @var string $pathname */
    private $pathname;

    /**
     * @param \Fluid\Fluid $fluid
     * @param null|string $pathname
     */
    public function __construct(Fluid $fluid, $pathname = null)
    {
        $this->setFluid($fluid);
        $this->setPathname($pathname);
    }

    /**
     * Route a request
     *
     * @param string|null $pathname
     * @return mixed
     */
    public function dispatch($pathname = null)
    {
        if (null !== $pathname) {
            $this->setPathname($pathname);
        } else {
            $pathname = $this->getPathname();
        }

        if (stripos($pathname, '/fluidcms/') === 0) {
            // Route admin requests
            if ($response = HTTP::route($pathname)) {
                return $response;
            }
        } else {
            // Route pages
            if (null === $pathname && isset($_SERVER['REQUEST_URI'])) {
                $pathname = $_SERVER['REQUEST_URI'];
            }

            $pathname = '/' . ltrim($pathname, '/');

            $map = new Map;
            $page = self::matchRequest($pathname, $map->getPages());

            if (isset($page) && false !== $page) {
                return $this->view($map, $page);
            }
        }

        return Fluid::NOT_FOUND;
    }

    /**
     * @param \Fluid\Map\Map $map
     * @param array $page
     * @return string
     */
    private function view(Map $map, array $page)
    {
        Data::setMap($map);
        $data = Data::get($page['id']);
        $layout = new Layout($page['layout']);
        return (new View($this->getFluid(), $map, $layout))->load($page, $data);
    }

    /**
     * Try to match a request with an array of pages
     *
     * @param string $request
     * @param array $pages
     * @param string $parent
     * @return array|bool
     */
    private static function matchRequest($request, array $pages, $parent = '')
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

    /**
     * @param \Fluid\Fluid $fluid
     * @return $this
     */
    public function setFluid(Fluid $fluid)
    {
        $this->fluid = $fluid;
        return $this;
    }

    /**
     * @return \Fluid\Fluid
     */
    public function getFluid()
    {
        return $this->fluid;
    }

    /**
     * @param null|string $pathname
     * @return $this
     */
    public function setPathname($pathname = null)
    {
        $this->pathname = $pathname;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getPathname()
    {
        return $this->pathname;
    }
}