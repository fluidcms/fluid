<?php

namespace Fluid\WebSockets\Tasks;

use Fluid;

class RequestedData extends Fluid\WebSockets\Task implements Fluid\WebSockets\TaskInterface
{
    protected $interval = 0;
    private $server;

    /**
     * Init the task
     *
     * @param   Fluid\WebSockets\Server $server
     */
    public function __construct(Fluid\WebSockets\Server $server)
    {
        $this->server = $server;
    }

    /**
     * Execute the task
     *
     * @return  void
     */
    public function execute()
    {
    }

    /**
     * Receive message from the task and broadcast it to users
     *
     * @param   array $data
     * @return  void
     */
    public function message(array $data)
    {
        foreach ($this->server->getConnections() as $connection => $subscribers) {
            foreach($subscribers as $subscriber) {
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