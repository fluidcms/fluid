<?php

namespace Fluid\WebSockets;

abstract class Task
{
    protected $interval;
    protected $lastExecutionTime;

    /**
     * Get the timestamp of the last execution
     *
     * @return int
     */
    public function getLastExecutionTime()
    {
        return $this->lastExecutionTime;
    }

    /**
     * Get the timestamp of the last execution
     *
     * @param   string  $time
     * @return  void
     */
    public function setExecutionTime($time)
    {
        $this->lastExecutionTime = $time;
    }

    /**
     * Get last executed
     *
     * @return int
     */
    public function getInterval()
    {
        return $this->interval;
    }
}