<?php
namespace Fluid;

use ReflectionClass;

class Response
{
    const RESPONSE_CODE_OK = 200;
    const RESPONSE_CODE_CREATED = 201;
    const RESPONSE_CODE_BAD_REQUEST = 400;
    const RESPONSE_CODE_UNAUTHORIZED = 401;
    const RESPONSE_CODE_FORBIDDEN = 403;
    const RESPONSE_CODE_NOT_FOUND = 404;
    const RESPONSE_CODE_METHOD_NOT_ALLOWED = 405;

    /**
     * @var string
     */
    private $body;

    /**
     * @var int
     */
    private $code = self::RESPONSE_CODE_OK;

    /**
     * @param $body
     * @return $this
     */
    public function json($body)
    {
        return $this->setBody(json_encode($body));
    }

    /**
     * @param string $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param null|string $body
     * @return $this|string
     */
    public function body($body = null)
    {
        if (null !== $body) {
            return $this->setBody($body);
        }
        return $this->getBody();
    }

    /**
     * @param int $code
     * @return $this
     */
    public function setCode($code)
    {
        $reflect = new ReflectionClass(get_class($this));
        $codes = $reflect->getConstants();
        foreach ($codes as $key => $value) {
            if (strpos($key, 'RESPONSE_CODE') === 0 && $value === $code) {
                $this->code = $code;
            }
        }
        return $this;
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param null|int $code
     * @return $this|int
     */
    public function code($code = null)
    {
        if (null !== $code) {
            return $this->setCode($code);
        }
        return $this->getCode();
    }
}