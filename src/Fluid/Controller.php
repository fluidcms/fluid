<?php
namespace Fluid;

use Fluid\Daemon\Daemon;

abstract class Controller
{
    /**
     * @var Fluid
     */
    protected $fluid;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var CookieInterface
     */
    protected $cookie;

    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * @var Daemon
     */
    protected $daemon;

    /**
     * @param Fluid $fluid
     * @param Router $router
     * @param Request $request
     * @param Response $response
     * @param StorageInterface $storage
     * @param CookieInterface $cookie
     */
    public function __construct(Fluid $fluid = null, Router $router, Request $request, Response $response, StorageInterface $storage, CookieInterface $cookie = null)
    {
        if (null !== $fluid) {
            $this->setFluid($fluid);
        }
        $this->setRouter($router);
        $this->setRequest($request);
        $this->setResponse($response);
        $this->setStorage($storage);
        if (null !== $cookie) {
            $this->setCookie($cookie);
        }
    }

    /**
     * @param string $view
     * @param array $data
     * @return null|string
     */
    protected function loadView($view, array $data = null)
    {
        $file = __DIR__ . "/Includes/templates/{$view}.php";
        if (file_exists($file)) {
            if (null !== $data) {
                foreach ($data as $key => $value) {
                    $GLOBALS[$key] = $value;
                }
            }
            ob_start();
            require $file;
            $contents = ob_get_contents();
            ob_end_clean();
            return $contents;
        }
        return null;
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
     * @param Router $router
     * @return $this
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * @return Router
     */
    public function getRouter()
    {
        return $this->router;
    }

    /**
     * @param CookieInterface $cookie
     * @return $this
     */
    public function setCookie(CookieInterface $cookie)
    {
        $this->cookie = $cookie;
        return $this;
    }

    /**
     * @return CookieInterface
     */
    public function getCookie()
    {
        return $this->cookie;
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
        return $this->response;
    }

    /**
     * @param StorageInterface $storage
     * @return $this
     */
    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * @return StorageInterface
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @param Daemon $daemon
     * @return $this
     */
    public function setDaemon(Daemon $daemon)
    {
        $this->daemon = $daemon;
        return $this;
    }

    /**
     * @return Daemon
     */
    public function getDaemon()
    {
        if (null === $this->daemon) {
            $this->createDaemon();
        }
        return $this->daemon;
    }

    /**
     * @return $this
     */
    private function createDaemon()
    {
        return $this->setDaemon($this->getFluid()->getDaemon());
    }
}