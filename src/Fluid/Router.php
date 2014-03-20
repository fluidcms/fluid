<?php
namespace Fluid;

use Closure;

class Router
{
    const PUBLIC_FILES_PATH = '/../../public';
    const DEVELOP_JAVASCRIPTS_PATH = '/../../javascripts';
    const DEVELOP_STYLESHEETS_PATH = '/../../stylesheets';
    const DEFAULT_IMAGES_PATH = '/images/';
    const DEFAULT_FILES_PATH = '/files/';
    const DEFAULT_ADMIN_PATH = '/admin/';

    /**
     * @var bool
     */
    private $useDevelop = false;

    /**
     * @var string
     */
    private $imagesPath = self::DEFAULT_IMAGES_PATH;

    /**
     * @var string
     */
    private $filesPath = self::DEFAULT_FILES_PATH;

    /**
     * @var string
     */
    private $adminPath = self::DEFAULT_ADMIN_PATH;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Closure[]
     */
    private $routes = [];

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->setRequest($request);
    }

    /**
     * @param string $method
     * @param string $uri
     * @param Closure $callback
     * @return $this
     */
    public function respond($method, $uri, $callback = null)
    {
        if (null === $callback && is_callable($uri)) {
            $callback = $uri;
            $uri = $method;
            $method = null;
        }

        $this->routes[$method][$uri] = $callback;

        return $this;
    }

    /**
     * Route a request
     *
     * @return mixed
     */
    public function dispatch()
    {
        $uri = $this->getRequest()->getUri();

        if (stripos($uri, $this->getAdminPath()) === 0) {
            $uri = preg_replace('{^' . rtrim($this->getAdminPath(), '/') . '}', '', $uri);

            /** @var Closure $routes */
            $routes = require __DIR__ . DIRECTORY_SEPARATOR . 'Routes.php';
            $routes($this, $this->getRequest(), $this->getRequest()->getCookie());

            $method = $this->request->getMethod();
            if (isset($this->routes[$method][$uri])) {
                return $this->routes[$method][$uri]();
            } elseif (isset($this->routes[null][$uri])) {
                return $this->routes[null][$uri]();
            } else {
                $file = $uri;
                $dir = realpath(__DIR__ . DIRECTORY_SEPARATOR . self::PUBLIC_FILES_PATH);
                if ($this->useDevelop() && stripos(substr($file, 1), basename(self::DEVELOP_JAVASCRIPTS_PATH)) === 0) {
                    $file = preg_replace('{^/' . basename(self::DEVELOP_JAVASCRIPTS_PATH) . '}', '', $file);
                    $dir = realpath(__DIR__ . DIRECTORY_SEPARATOR . self::DEVELOP_JAVASCRIPTS_PATH);
                } elseif ($this->useDevelop() && stripos(substr($file, 1), basename(self::DEVELOP_STYLESHEETS_PATH)) === 0) {
                    $file = preg_replace('{^/' . basename(self::DEVELOP_STYLESHEETS_PATH) . '}', '', $file);
                    $dir = realpath(__DIR__ . DIRECTORY_SEPARATOR . self::DEVELOP_STYLESHEETS_PATH);
                }
                $file = $dir . $file;
                if (file_exists($file)) {
                    return new StaticFile($file);
                }
            }
        } elseif (stripos($uri, $this->getImagesPath()) === 0) {
            die('images');
        } elseif (stripos($uri, $this->getFilesPath()) === 0) {
            die('files');
        } else {
            return null;
            die('try pages');
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

        return null;
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
     * @param Request $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param string $adminPath
     * @return $this
     */
    public function setAdminPath($adminPath)
    {
        $this->adminPath = $adminPath;
        return $this;
    }

    /**
     * @return string
     */
    public function getAdminPath()
    {
        return $this->adminPath;
    }

    /**
     * @param string $filesPath
     * @return $this
     */
    public function setFilesPath($filesPath)
    {
        $this->filesPath = $filesPath;
        return $this;
    }

    /**
     * @return string
     */
    public function getFilesPath()
    {
        return $this->filesPath;
    }

    /**
     * @param string $imagesPath
     * @return $this
     */
    public function setImagesPath($imagesPath)
    {
        $this->imagesPath = $imagesPath;
        return $this;
    }

    /**
     * @return string
     */
    public function getImagesPath()
    {
        return $this->imagesPath;
    }

    /**
     * @param bool $useDevelop
     * @return $this
     */
    public function setUseDevelop($useDevelop)
    {
        $this->useDevelop = $useDevelop;
        return $this;
    }

    /**
     * @return bool
     */
    public function getUseDevelop()
    {
        return $this->useDevelop;
    }

    /**
     * @return bool
     */
    public function useDevelop()
    {
        return $this->getUseDevelop();
    }
}