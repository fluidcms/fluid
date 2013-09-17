<?php
/*
namespace Fluid;

class Task
{
    /*
     * Send a job to the gearman server
     *
     * @param   string  $task
     * @param   array   $data
     * @return  void
     *//*
    public static function execute($task, $data = [], $unique = null)
    {
        $client = new \GearmanClient();
        $client->addServers("127.0.0.1");
        $client->doLowBackground($task, json_encode($data), $unique);
    }
}*/