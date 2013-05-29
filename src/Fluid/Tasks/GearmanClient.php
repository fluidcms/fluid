<?php

namespace Fluid\Tasks;


class GearmanClient
{
    /*
     * Send a job to the gearman server
     *
     * @param   string  $task
     * @param   array   $data
     */
    public function __construct($task, $data = [])
    {
        $client = new \GearmanClient();
        $client->addServers("127.0.0.1:4730");
        $client->doLowBackground($task, json_encode($data));
    }

    public static function run($task, $data = [])
    {
        return new self($task, $data);
    }
}