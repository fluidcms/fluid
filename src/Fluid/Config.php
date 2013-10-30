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
     * Set config
     *
     * @param $name
     * @param $value
     */
    public static function set($name, $value)
    {
        self::$configs[$name] = $value;
    }

    /**
     * Get config
     *
     * @param $name
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