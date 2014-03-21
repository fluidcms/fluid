<?php
namespace Fluid;

abstract class Controller
{
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
     * @param Request $request
     * @param Response $response
     * @param StorageInterface $storage
     * @param CookieInterface $cookie
     */
    public function __construct(Request $request, Response $response, StorageInterface $storage, CookieInterface $cookie)
    {
        $this->setRequest($request);
        $this->setResponse($response);
        $this->setStorage($storage);
        $this->setCookie($cookie);
    }

    /**
     * @param $view
     * @return null|string
     */
    protected function loadView($view)
    {
        $file = __DIR__ . "/Includes/templates/{$view}.php";
        if (file_exists($file)) {
            ob_start();
            require $file;
            $contents = ob_get_contents();
            ob_end_clean();
            return $contents;
        }
        return null;
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
}