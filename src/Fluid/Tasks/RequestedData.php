<?php

namespace Fluid\Tasks;

use Fluid;
use Fluid\WebSocket\Server;

class RequestedData extends Fluid\Task implements Fluid\TaskInterface
{
    protected $interval = 0;
    private $server;

    /**
     * Init the task
     *
     * @param Server $server
     */
    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    /**
     * Execute the task
     */
    public function execute()
    {
    }

    /**
     * Receive message from the task and broadcast it to users
     *
     * @param array $data
     * @return void
     */
    public function message(array $data)
    {
        foreach ($this->server->getConnections() as $connection => $subscribers) {
            foreach ($subscribers as $subscriber) {
                if ($subscriber['session'] == $data['session']) {
                    $subscriber['topic']->broadcast(json_encode($data['message']));
                }
            }
        }
    }

    /**
     * Get key
     *
     * @return string
     */
    public function getKey()
    {
        return "RequestedData";
    }
}