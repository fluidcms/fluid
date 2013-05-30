<?php

namespace Fluid\Daemons;

use GearmanWorker, Fluid;

class Gearman
{
    private $status;
    private $lines = 0;
    private $timeStart;
    public $amount = 0;

    /*
     * Gearman Daemon
     *
     * @param   function    A callback to know if the daemon is still running, called at least every 10 seconds
     */
    public function __construct($upTimeCallback = null) {
        $this->status = file_get_contents(__DIR__ . '/GearmanStatus.txt');
        $this->timeStart = time();
        $this->upTimeCallback = $upTimeCallback;
    }

    /*
     * Create gearman server for Fluid
     *
     * @return  void
     */
    public function run()
    {
        $this->displayStatus();

        if (is_callable($this->upTimeCallback)) {
            call_user_func($this->upTimeCallback);
        }

        $worker = new GearmanWorker();

        $worker->addServer('127.0.0.1');

        $worker->addFunction('WatchBranchStatus', function($job) {
            $workload = json_decode($job->workload(), true);
            Fluid\Tasks\WatchBranchStatus::execute($workload[0], $workload[1], (isset($workload[2]) ? $workload[2] : false));
            return true;
        });

        $worker->addFunction('CommitPush', function($job) {
            $workload = json_decode($job->workload(), true);
            Fluid\Tasks\CommitPush::execute($workload[0], $workload[1]);
            return true;
        });

        // Expire every 10 seconds and call callback
        $worker->setTimeout(10 * 1000);

        while (true) {
            while ($worker->work()) {
                $this->amount++;

                if (is_callable($this->upTimeCallback)) {
                    call_user_func($this->upTimeCallback);
                }

                $this->displayStatus();
            }

            if (is_callable($this->upTimeCallback)) {
                call_user_func($this->upTimeCallback);
            }

            $this->displayStatus();
        }
    }

    /**
     * Display daemon status
     *
     * @return  void
     */
    public function displayStatus()
    {
        $clear = '';
        for($i = 0; $i < $this->lines; $i++) {
            $clear .= '\033[A';
        }

        passthru('printf "'.$clear.'"');

        $status = $this->status;
        $status = str_replace('%uptime', floor((time() - $this->timeStart)/60), $status);
        $status = str_replace('%amount', $this->amount, $status);
        passthru('echo "'.$status.'"');

        $lines = explode(PHP_EOL, $status);
        $this->lines = count($lines);
    }
}