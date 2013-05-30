<?php

namespace Fluid\Tasks;

use GearmanWorker;

class GearmanServer
{
    /*
     * Create gearman server for Fluid
     */
    public function __construct()
    {
        $worker = new GearmanWorker();
        $worker->addServer('127.0.0.1');

        $worker->addFunction('watchBranchStatus', function($job) {
            $workload = json_decode($job->workload(), true);
            WatchBranchStatus::run($workload[0], $workload[1], (isset($workload[2]) ? $workload[2] : false));
            return true;
        });

        $worker->addFunction('CommitPush', function($job) {
            $workload = json_decode($job->workload(), true);
            CommitPush::run($workload[0], $workload[1]);
            return true;
        });

        while ($worker->work());
    }

    public static function run()
    {
        return new self();
    }
}