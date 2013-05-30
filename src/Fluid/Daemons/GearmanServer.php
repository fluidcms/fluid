<?php

namespace Fluid\Daemons;

use GearmanWorker, Fluid;

class GearmanServer
{
    /*
     * Create gearman server for Fluid
     *
     * @return  void
     */
    public static function run()
    {
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

        while ($worker->work());
    }
}