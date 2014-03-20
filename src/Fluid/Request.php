<?php
namespace Fluid;

/**
 * Route requests to pages.
 *
 * @package fluid
 */
class Request
{
    const HTTP_NOT_FOUND = 404;

    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';

    /**
     * @var string
     */
    private $uri;

    /**
     * @var array
     */
    private $params = [];

    /**
     * @var string
     */
    private $method;

    /**
     * @var CookieInterface
     */
    private $cookie;

    /**
     * @param string $uri
     * @return $this
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
        return $this;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        if (null === $this->uri) {
            $this->createUri();
        }

        return $this->uri;
    }

    /**
     * @return $this
     */
    private function createUri()
    {
        $uri = $_SERVER['REQUEST_URI'];
        if ($pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        return $this->setUri($uri);
    }

    /**
     * @param array $params
     * @return $this
     */
    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        if (null === $this->params) {
            $this->createParams();
        }

        return $this->params;
    }

    /**
     * @param array|null $mask
     * @return array
     */
    public function params(array $mask = null)
    {
        $params = $this->getParams();
        $retval = [];
        foreach ($mask as $key) {
            $retval[$key] = isset($params[$key]) ? $params[$key] : null;
        }
        return $retval;
    }

    /**
     * @return $this
     */
    private function createParams()
    {
        $params = array_merge($_GET, $_POST, $_REQUEST);
        if (!count($_POST)) {
            $params = file_get_contents('php://input');
            if (is_string($params)) {
                $bodyParams = json_decode($params, true);
                if (!is_array($bodyParams) && is_string($params)) {
                    preg_match_all('/[ &]?([^ =&]+)=["|\']?([^"\',&]+)["|\']?/', $params, $matches);
                    if (isset($matches[1]) && isset($matches[2]) && count($matches[1])) {
                        $bodyParams = [];
                        foreach ($matches[1] as $count => $key) {
                            $bodyParams[$key] = urldecode(isset($matches[2][$count]) ? $matches[2][$count] : null);
                        }
                    }
                }
                if (is_array($bodyParams)) {
                    $params = $bodyParams;
                }
            }
        }
        return $this->setParams($params);
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
        if (null === $this->cookie) {
            $this->createCookie();
        }

        return $this->cookie;
    }

    /**
     * @return $this
     */
    public function createCookie()
    {
        return $this->setCookie(new Cookie);
    }

    /**
     * @param string $method
     * @return $this
     */
    public function setMethod($method)
    {
        if (
            $method === self::METHOD_GET ||
            $method === self::METHOD_DELETE ||
            $method === self::METHOD_POST ||
            $method === self::METHOD_PUT
        ) {
            $this->method = $method;
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        if (null === $this->method) {
            $this->createMethod();
        }

        return $this->method;
    }

    /**
     * @return $this
     */
    public function createMethod()
    {
        $this->setMethod($_SERVER['REQUEST_METHOD']);
    }
}