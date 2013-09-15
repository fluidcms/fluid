<?php

namespace Fluid\WebSockets\Tasks;

use Fluid;

class WatchRemote extends Fluid\WebSockets\Task implements Fluid\WebSockets\TaskInterface
{
    protected $interval = 10;
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
        $connections = $this->server->getConnections();
        if (count($this->server->getConnections())) {
            $branches = array();
            foreach ($connections as $subscribers) {
                foreach ($subscribers as $subscriber) {
                    $branches[$subscriber['branch']] = true;
                }
            }
            foreach ($branches as $branch => $value) {
                Fluid\Task::execute('Fetch', array($branch), 'WatchRemote/' . $branch);
            }
        }
    }

    /**
     * Receive message from the task and broadcast it to users
     *
     * @param   array $data
     * @return  void
     */
    public function message(array $data)
    {
    }

    /**
     * Get key
     *
     * @return string
     */
    public function getKey()
    {
        return "WatchRemote";
    }
}