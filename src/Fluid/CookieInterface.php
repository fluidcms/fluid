<?php
namespace Fluid;

interface CookieInterface
{
    /**
     * @param string $name
     */
    public function get($name);

    /**
     * @param string $name
     * @param string|null $value
     * @param int|null $expire
     * @param string|null $path
     * @param string|null $domain
     * @param bool|null $secure
     * @return bool
     */
    public function save($name, $value = null, $expire = null, $path = null, $domain = null, $secure = null);

    /**
     * @param string $name
     * @param string|null $path
     * @param string|null $domain
     * @param bool|null $secure
     * @return bool
     */
    public function delete($name, $path = null, $domain = null, $secure = null);
}