<?php
namespace Fluid;

abstract class Controller
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var CookieInterface
     */
    protected $cookie;

    /**
     * @param Request $request
     * @param CookieInterface $cookie
     */
    public function __construct(Request $request, CookieInterface $cookie)
    {
        $this->setRequest($request);
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
}