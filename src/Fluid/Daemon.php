<?php

namespace Fluid;

abstract class Daemon
{
    protected $displayStatus = false;
    protected $statusFile;
    protected $status;
    protected $timeStart;
    private $lines = 0;

    /*
     * Init the daemon
     *
     * @param   function    A callback to know if the daemon is still running, called every 10 seconds
     */
    public function __construct($upTimeCallback = null, $displayStatus = false)
    {
        $this->displayStatus = $displayStatus;
        if ($this->displayStatus) {
            if (isset($this->statusFile)) {
                $this->status = file_get_contents(__DIR__ . '/Daemons/' . $this->statusFile);
            }
        }
        $this->timeStart = time();
        $this->upTimeCallback = $upTimeCallback;
    }

    /**
     * Display the daemon status
     *
     * @param   string  $status
     * @return  void
     */
    protected function displayStatus($status)
    {
        if ($this->displayStatus) {
            $clear = '';
            for($i = 0; $i < $this->lines; $i++) {
                $clear .= '\033[A';
            }

            passthru('printf "'.$clear.'"');
            foreach (explode(PHP_EOL, $status) as $line) {
                passthru('echo "'.$line.'                                  "');
            }

            $lines = explode(PHP_EOL, $status);
            $this->lines = count($lines);
        }
    }

    /**
     * Get the up time in a readable format
     *
     * @param   bool    $minutes Starts with minutes instead of seconds
     * @return  string
     */
    public function getReadableUpTime($minutes = false)
    {
        $upTime = time() - $this->timeStart;
        if ($upTime < 60 && !$minutes) {
            return $upTime . " seconds";
        } else if ($upTime < 3600) {
            return floor($upTime/60) . " minutes";
        } else if ($upTime < 86400) {
            return floor($upTime/60/60) . " hours";
        } else {
            return floor($upTime/60/60/24) . " days";
        }
    }

    /**
     * Get the memory usage in a readable format
     *
     * @return  string
     */
    public function getReadableMemoryUsage()
    {
        $memory = memory_get_usage() / 1024;
        return round($memory).'K';

    }

    /**
     * Execute the up time callback
     *
     * @return  void
     */
    protected function upTimeCallback()
    {
        if (isset($this->upTimeCallback) && is_callable($this->upTimeCallback)) {
            call_user_func($this->upTimeCallback);
        }
    }
}