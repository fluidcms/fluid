<?php

namespace Fluid\WebSockets;

use Fluid;

class Tasks
{
    private $server;
    private $tasks = array();

    /**
     * Create tasks
     */
    public function __construct(Server $server)
    {
        $this->server = $server;
        $this->register(new Fluid\WebSockets\Tasks\WatchBranchStatus($server));
    }

    /**
     * Register a task
     *
     * @param   TaskInterface $task
     * @return  void
     */
    public function register(TaskInterface $task)
    {
        $this->tasks[$task->getKey()] = [$task, $task->getInterval()];
    }

    /**
     * Execute all tasks
     *
     * @return  void
     */
    public function execute()
    {
        $time = time();

        foreach($this->tasks as $task) {
            $interval = $task[1];
            $task = $task[0];

            if ($time - $task->getLastExecutionTime() >= $interval) {
                $task->execute();
                $task->setExecutionTime($time);
            }
        }
    }

    /**
     * Receive messages and dispatch it to the tasks
     *
     * @param   array   $data
     * @return  void
     */
    public function message($data)
    {
        $data = json_decode($data, true);
        if (isset($this->tasks[$data['task']])) {
            $this->tasks[$data['task']][0]->message($data['data']);
        }
    }
}
