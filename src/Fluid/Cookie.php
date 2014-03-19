<?php
namespace Fluid;

class Cookie implements CookieInterface
{
    /**
     * @param string $name
     * @return mixed|null
     */
    public function get($name)
    {
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
    }

    /**
     * @param string $name
     * @param string|null $value
     * @param int|null $expire
     * @param string|null $path
     * @param string|null $domain
     * @param bool|null $secure
     * @return bool
     */
    public function save($name, $value = null, $expire = null, $path = null, $domain = null, $secure = null)
    {
        return setcookie($name, $value, $expire, $path, $domain, $secure);
    }

    /**
     * @param string $name
     * @param string|null $path
     * @param string|null $domain
     * @param bool|null $secure
     * @return bool
     */
    public function delete($name, $path = null, $domain = null, $secure = null)
    {
        return setcookie($name, null, time() - 3600, $path, $domain, $secure);
    }
}