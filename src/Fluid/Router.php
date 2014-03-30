<?php
namespace Fluid;

use Closure;
use Fluid\Session\SessionCollection;
use Fluid\Session\SessionEntity;
use Fluid\User\UserCollection;
use Fluid\User\UserEntity;

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
     * @var Fluid
     */
    private $fluid;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var Closure[]
     */
    private $routes = [];

    /**
     * @param Request $request
     * @param Response $response
     * @param Fluid|null $fluid
     */
    public function __construct(Request $request, Response $response = null, Fluid $fluid = null)
    {
        $this->setRequest($request);
        if ($response !== null) {
            $this->setResponse($response);
        }
        if ($fluid !== null) {
            $this->setFluid($fluid);
        }
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

        $this->routes[$uri][$method] = $callback;

        return $this;
    }

    /**
     * Route a request
     *
     * @return null|Response
     */
    public function dispatch()
    {
        $uri = $this->getRequest()->getUri();
        $response = $this->getResponse();
        $found = false;

        if (stripos($uri, $this->getAdminPath()) === 0) {
            $uri = preg_replace('{^' . rtrim($this->getAdminPath(), '/') . '}', '', $uri);

            /** @var Closure $routes */
            $routes = require __DIR__ . DIRECTORY_SEPARATOR . 'Routes' . DIRECTORY_SEPARATOR . 'HttpRoutes.php';
            $routes($this->getFluid(), $this, $this->getRequest(), $response, $this->getFluid()->getStorage(), $this->getRequest()->getCookie());

            $method = $this->request->getMethod();
            if (isset($this->routes[$uri])) {
                if (isset($this->routes[$uri][$method])) {
                    $found = true;
                    $this->routes[$uri][$method]();
                } elseif (isset($this->routes[$uri][null])) {
                    $found = true;
                    $this->routes[$uri][null]();
                } else {
                    $response->setCode(Response::RESPONSE_CODE_METHOD_NOT_ALLOWED);
                }
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
                    $found = true;
                    $response->code(Response::RESPONSE_CODE_OK);
                    new StaticFile($file);
                }
            }
        } elseif (stripos($uri, $this->getImagesPath()) === 0) {
            $found = true;
            die('images');
        } elseif (stripos($uri, $this->getFilesPath()) === 0) {
            $found = true;
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

        if ($found) {
            return $response;
        }
        return null;
    }

    /**
     * Route a local websocket request
     *
     * @param StorageInterface $storage
     * @param UserCollection $users
     * @param UserEntity $user
     * @param SessionCollection $sessions
     * @param SessionEntity $session
     * @return null|Response
     */
    public function dispatchLocalWebsocketRouter(StorageInterface $storage, UserCollection $users, UserEntity $user, SessionCollection $sessions, SessionEntity $session)
    {
        $uri = $this->getRequest()->getUri();
        $response = $this->getResponse();
        $found = false;

        /** @var callable $routes */
        $routes = require __DIR__ . DIRECTORY_SEPARATOR . 'Routes' . DIRECTORY_SEPARATOR . 'LocalWebsocketRoutes.php';
        $routes($this->getFluid(), $this, $this->getRequest(), $this->getResponse(), $storage, $users, $user, $sessions, $session);

        $method = $this->request->getMethod();
        if (isset($this->routes[$uri])) {
            if (isset($this->routes[$uri][$method])) {
                $found = true;
                $this->routes[$uri][$method]();
            } elseif (isset($this->routes[$uri][null])) {
                $found = true;
                $this->routes[$uri][null]();
            } else {
                $response->setCode(Response::RESPONSE_CODE_METHOD_NOT_ALLOWED);
            }
        }

        if ($found) {
            return $response;
        }
        return null;
    }

    //////------------

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

    /**
     * @param Fluid $fluid
     * @return $this
     */
    public function setFluid(Fluid $fluid)
    {
        $this->fluid = $fluid;
        return $this;
    }

    /**
     * @return Fluid
     */
    public function getFluid()
    {
        return $this->fluid;
    }

    /**
     * @param Response $response
     * @return $this
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        if (null === $this->response) {
            $this->createResponse();
        }
        return $this->response;
    }

    /**
     * @return $this
     */
    private function createResponse()
    {
        return $this->setResponse(new Response);
    }
}