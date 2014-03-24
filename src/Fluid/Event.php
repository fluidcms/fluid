<?php
namespace Fluid;

use InvalidArgumentException;

class Event
{
    /**
     * @var array
     */
    private $listeners = [];

    /**
     * Bind an event.
     *
     * @param string $event
     * @param callable $callback
     * @throws InvalidArgumentException
     * @return bool
     */
    public function on($event, callable $callback)
    {
        // Check if event is a string
        if (!is_string($event) || !is_callable($callback)) {
            throw new InvalidArgumentException();
        }

        if (!isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }

        $this->listeners[$event][] = $callback;

        return true;
    }

    /**
     * Trigger an event
     *
     * @param string $event
     * @param array $arguments
     * @throws InvalidArgumentException
     * @return array
     */
    public function trigger($event, array $arguments = [])
    {
        if (!is_string($event) || !is_array($arguments)) {
            throw new InvalidArgumentException();
        }

        $retval = [];

        if (isset($this->listeners[$event])) {
            foreach ($this->listeners[$event] as $listener) {
                $retval[] = call_user_func_array($listener, $arguments);
            }
        }

        return $retval;
    }
}