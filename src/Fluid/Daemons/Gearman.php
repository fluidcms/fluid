<?php

namespace Fluid\Daemons;

use GearmanWorker, Fluid;

class Gearman extends Fluid\Daemon implements Fluid\DaemonInterface
{
    protected $statusFile = 'GearmanStatus.txt';
    public $amount = 0;

    /*
     * Create gearman server for Fluid
     *
     * @return  void
     */
    public function run()
    {
        $this->upTimeCallback();
        $this->renderStatus();

        $worker = new GearmanWorker();

        $worker->addServer('127.0.0.1');

        $worker->addFunction('BranchStatus', function($job) {
            $workload = json_decode($job->workload(), true);
            call_user_func_array(array("\\Fluid\\Tasks\\BranchStatus", "execute"), $workload);
            return true;
        });

        $worker->addFunction('Fetch', function($job) {
            $workload = json_decode($job->workload(), true);
            call_user_func_array(array("\\Fluid\\Tasks\\Fetch", "execute"), $workload);
            return true;
        });

        $worker->addFunction('FetchPull', function($job) {
            $workload = json_decode($job->workload(), true);
            call_user_func_array(array("\\Fluid\\Tasks\\FetchPull", "execute"), $workload);
            return true;
        });

        $worker->addFunction('CommitPush', function($job) {
            $workload = json_decode($job->workload(), true);
            call_user_func_array(array("\\Fluid\\Tasks\\CommitPush", "execute"), $workload);
            return true;
        });

        // Expire every 10 seconds and call callback
        $worker->setTimeout(10 * 1000);

        while (true) {
            while ($worker->work()) {
                $this->amount++;
                $this->renderStatus();
            }

            $this->upTimeCallback();
            $this->renderStatus();
        }
    }

    /**
     * Display daemon status
     *
     * @return  void
     */
    public function renderStatus()
    {
        $status = $this->status;
        $status = str_replace('%uptime', $this->getReadableUpTime(true), $status);
        $status = str_replace('%amount', $this->amount, $status);
        $status = str_replace('%memory', $this->getReadableMemoryUsage(), $status);
        $this->displayStatus($status);
    }
}