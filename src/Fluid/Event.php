<?php

namespace Fluid;

use InvalidArgumentException;

class Event
{
    private static $listeners = array();

    /**
     * Bind an event.
     *
     * @param   string      $event
     * @param   callable    $callback
     * @throws  InvalidArgumentException
     * @return  bool
     */
    public static function on($event, $callback)
    {
        // Check if event is a string
        if (!is_string($event) || !is_callable($callback)) {
            throw new InvalidArgumentException();
        }

        if (!isset(self::$listeners[$event])) {
            self::$listeners[$event] = array();
        }

        self::$listeners[$event][] = $callback;

        return true;
    }

    /**
     * Trigger an event
     *
     * @param   string  $event
     * @param   array   $arguments
     * @throws  InvalidArgumentException
     * @return  array
     */
    public static function trigger($event, $arguments = array())
    {
        if (!is_string($event) || !is_array($arguments)) {
            throw new InvalidArgumentException();
        }

        $retval = array();

        if (isset(self::$listeners[$event])) {
            foreach (self::$listeners[$event] as $listener) {
                $retval[] = call_user_func_array($listener, $arguments);
            }
        }

        return $retval;
    }
}