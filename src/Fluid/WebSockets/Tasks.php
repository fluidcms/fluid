<?php

namespace Fluid\WebSockets;

use Fluid;

class Tasks
{
    private $server;

    /**
     * Create tasks
     */
    public function __construct(Server $server) {
        $this->server = $server;
    }

    /**
     * Execute all tasks
     *
     * @return  void
     */
    public function execute() {
    }
}
