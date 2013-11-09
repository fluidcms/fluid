<?php

namespace Fluid;

/**
 * Class Config
 * @package Fluid
 */
class Config
{
    /** @var array $configs */
    private static $configs = array();

    /**
     * @param array $configs
     */
    public static function setAll(array $configs)
    {
        self::$configs = $configs;
    }

    /**
     * @return array
     */
    public static function getAll()
    {
        return self::$configs;
    }

    /**
     * Set config
     *
     * @param string $name
     * @param mixed $value
     */
    public static function set($name, $value)
    {
        self::$configs[$name] = $value;
    }

    /**
     * Get config
     *
     * @param string $name
     * @return mixed
     */
    public static function get($name)
    {
        // Unit Tests config override
        if (isset($GLOBALS["fluid.{$name}"])) {
            return $GLOBALS["fluid.{$name}"];
        }

        return self::$configs[$name];
    }
}