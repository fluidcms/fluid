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

    /**
     * @var string
     */
    private $uri;

    /**
     * @var array
     */
    private $params = [];

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
        return $this->setUri($_SERVER['REQUEST_URI']);
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
}