<?php
namespace Fluid;

use Closure;
use Fluid\File\FileEntity;
use Fluid\Page\PageCollection;
use Fluid\Session\SessionCollection;
use Fluid\Session\SessionEntity;
use Fluid\User\UserCollection;
use Fluid\User\UserEntity;
use Fluid\Page\PageEntity;

class Router
{
    const EVENT_BEFORE_ROUTING = 'beforeRouting';
    const EVENT_AFTER_ROUTING = 'afterRouting';

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
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @var ConfigInterface
     */
    private $config;

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
     * @param RegistryInterface $registry
     * @param ConfigInterface $config
     * @param Request $request
     * @param Response $response
     */
    public function __construct(RegistryInterface $registry, ConfigInterface $config, Request $request, Response $response = null)
    {
        $this->setRegistry($registry);
        $this->setConfig($config);
        $this->setRequest($request);
        if ($response !== null) {
            $this->setResponse($response);
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
     * @param string $uri
     * @return Response
     */
    private function routeAdmin($uri)
    {
        $found = false;
        if (stripos($uri, $this->getAdminPath()) === 0) {
            $uri = preg_replace('{^' . rtrim($this->getAdminPath(), '/') . '}', '', $uri);

            /** @var Closure $routes */
            $routes = require __DIR__ . DIRECTORY_SEPARATOR . 'Routes' . DIRECTORY_SEPARATOR . 'HttpRoutes.php';
            $routes($this->getRegistry(), $this->getConfig(), $this, $this->getRequest(), $this->getResponse(), $this->getRegistry()->getStorage(), $this->getRegistry()->getXmlMappingLoader(), $this->getRequest()->getCookie());

            $method = $this->request->getMethod();
            foreach ($this->routes as $path => $methods) {
                $path = str_replace('/', '\/', $path);
                $match = preg_match_all('/^' . $path . '$/i', $uri, $matches);
                if ($match) {
                    array_shift($matches);
                    $arguments = [];
                    if (is_array($matches)) {
                        foreach ($matches as $match) {
                            if (isset($match[0])) {
                                $arguments[] = $match[0];
                            }
                        }
                    }
                    if (isset($methods[$method])) {
                        $found = true;
                        $this->getResponse()->setCode(Response::RESPONSE_CODE_OK);
                        call_user_func_array($methods[$method], $arguments);
                    } elseif (isset($methods[null])) {
                        $found = true;
                        $this->getResponse()->setCode(Response::RESPONSE_CODE_OK);
                        call_user_func_array($methods[null], $arguments);
                    } else {
                        $this->getResponse()->setCode(Response::RESPONSE_CODE_METHOD_NOT_ALLOWED);
                    }
                }
            }

            if (!$found) {
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
                    $this->getResponse()->code(Response::RESPONSE_CODE_OK);
                    new StaticFile($file);
                }
            }
        }
        if ($found && $this->getResponse()->code() !== Response::RESPONSE_CODE_NOT_FOUND) {
            return $this->getResponse();
        }
        return null;
    }

    /**
     * @param string $uri
     * @return null
     */
    private function routeImages($uri)
    {
        if (stripos($uri, $this->getImagesPath()) === 0) {
            $args = explode('/', substr($uri, strlen($this->getImagesPath())));
            $file = new FileEntity($this->getRegistry());
            $file->setId(urldecode($args[0]));
            $file->setName(urldecode(end($args)));
            if ($file->exists()) {
                $this->getResponse()->setCode(Response::RESPONSE_CODE_OK);
                $this->getResponse()->setBody($file->render());
                return $this->getResponse();
            }
        }
        return null;
    }

    /**
     * @param string $uri
     * @return null
     */
    private function routeFiles($uri)
    {
        if (stripos($uri, $this->getFilesPath()) === 0) {
            die('files');
        }
        return null;
    }

    /**
     * @param string $request
     * @param PageCollection|PageEntity[] $pages
     * @return array|bool
     */
    private function findPage($request, PageCollection $pages)
    {
        foreach ($pages as $page) {
            if ($request === $page->getConfig()->getUrl()) {
                return $page;
            } elseif ($page->hasPages()) {
                $match = $this->findPage($request, $page->getPages());
                if ($match instanceof PageEntity) {
                    return $match;
                }
            }
        }
        return false;
    }

    /**
     * @param string $uri
     * @return Response
     */
    private function routePages($uri)
    {
        $map = $this->getRegistry()->getMap();
        $page = $this->findPage($uri, $map->getPages());

        if ($page instanceof PageEntity) {
            $this->getResponse()->setCode(Response::RESPONSE_CODE_OK);
            $this->getResponse()->setBody($page->render());
            return $this->getResponse();
        }

        return null;
    }

    /**
     * Route a request
     *
     * @return null|Response
     */
    public function dispatch()
    {
        $this->getRegistry()->getEventDispatcher()->trigger($this, self::EVENT_BEFORE_ROUTING);
        $uri = $this->getRequest()->getUri();
        $this->getResponse()->setCode(Response::RESPONSE_CODE_NOT_FOUND);

        $this->routeAdmin($uri) ||
        $this->routeImages($uri) ||
        $this->routeFiles($uri) ||
        $this->routePages($uri);

        $this->getRegistry()->getEventDispatcher()->trigger($this, self::EVENT_AFTER_ROUTING);
        if ($this->getResponse()->getCode() !== Response::RESPONSE_CODE_NOT_FOUND) {
            return $this->getResponse();
        }
        return null;
    }

    /**
     * Route a local websocket request
     *
     * @param StorageInterface $storage
     * @param XmlMappingLoaderInterface $xmlMappingLoader
     * @param UserCollection $users
     * @param UserEntity $user
     * @param SessionCollection $sessions
     * @param SessionEntity $session
     * @return null|Response
     */
    public function dispatchLocalWebsocketRouter(StorageInterface $storage, XmlMappingLoaderInterface $xmlMappingLoader, UserCollection $users, UserEntity $user, SessionCollection $sessions, SessionEntity $session)
    {
        $uri = $this->getRequest()->getUri();
        $response = $this->getResponse();
        $found = false;

        /** @var callable $routes */
        $routes = require __DIR__ . DIRECTORY_SEPARATOR . 'Routes' . DIRECTORY_SEPARATOR . 'LocalWebsocketRoutes.php';
        $routes($this->getRegistry(), $this->getConfig(), $this, $this->getRequest(), $this->getResponse(), $storage, $xmlMappingLoader, $users, $user, $sessions, $session);

        $method = $this->request->getMethod();
        foreach ($this->routes as $path => $methods) {
            $path = str_replace('/', '\/', $path);
            $match = preg_match_all('/^' . $path . '$/i', $uri, $matches);
            if ($match) {
                array_shift($matches);
                $arguments = [];
                if (is_array($matches)) {
                    foreach ($matches as $match) {
                        if (isset($match[0])) {
                            $arguments[] = $match[0];
                        }
                    }
                }
                if (isset($methods[$method])) {
                    $found = true;
                    call_user_func_array($methods[$method], $arguments);
                } elseif (isset($methods[null])) {
                    $found = true;
                    call_user_func_array($methods[null], $arguments);
                } else {
                    $response->setCode(Response::RESPONSE_CODE_METHOD_NOT_ALLOWED);
                }
            }
        }

        if ($found) {
            return $response;
        }
        return null;
    }

    /**
     * @param ConfigInterface $config
     * @return $this
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @return ConfigInterface
     */
    public function getConfig()
    {
        return $this->config;
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

    /**
     * @return RegistryInterface
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * @param RegistryInterface $registry
     * @return $this
     */
    public function setRegistry(RegistryInterface $registry)
    {
        $this->registry = $registry;
        return $this;
    }
}