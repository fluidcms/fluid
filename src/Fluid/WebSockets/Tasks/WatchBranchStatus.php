<?php

namespace Fluid\WebSockets\Tasks;

use Fluid;

class WatchBranchStatus extends Fluid\WebSockets\Task implements Fluid\WebSockets\TaskInterface
{
    protected $interval = 1;
    private $server;
    private $lastMessageSent = array();

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
            foreach($connections as $subscribers) {
                foreach($subscribers as $subscriber) {
                    $branches[$subscriber['branch']] = true;
                }
            }
            foreach($branches as $branch => $value) {
                Fluid\Task::execute('BranchStatus', array($branch), 'WatchBranchStatus/' . $branch);
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
        foreach ($this->server->getConnections() as $connection => $subscribers) {
            foreach($subscribers as $subscriber) {
                if ($subscriber['branch'] == $data['branch']) {
                    $msg = json_encode($data['message']);
                    if (!isset($this->lastMessageSent[$connection]) || $this->lastMessageSent[$connection] !== $msg) {
                        $subscriber['user']->broadcast($msg);
                    }
                    $this->lastMessageSent[$connection] = $msg;
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
        return "WatchBranchStatus";
    }
}