<?php

namespace Fluid\Daemons;

use Fluid,
    React,
    Ratchet,
    ZMQ,
    ZMQSocketException;

class WebSocket extends Fluid\Daemon implements Fluid\DaemonInterface
{
    private $instanceId;
    private $lock;
    protected $statusFile = 'WebSocketStatus.txt';

    /**
     * Create and lock the lock file
     *
     * @return  mixed
     */
    public function lock()
    {
        $dir = Fluid\Fluid::getConfig('storage') . ".data/";
        if (!is_dir($dir)) {
            mkdir($dir);
        }

        $this->lock = fopen($dir."server.lock", "w+");

        if (flock($this->lock, LOCK_EX | LOCK_NB)) {
            return true;
        }

        return false;
    }

    /**
     * Release the lock file
     *
     * @return  mixed
     */
    public function release()
    {
        flock($this->lock, LOCK_UN);
        fclose($this->lock);
    }

    /*
     * Run the web socket server
     *
     * @return  void
     */
    public function run()
    {
        if (!$this->lock()) {
            return;
        }

        $this->instanceId = uniqid();
        $this->upTimeCallback();

        $ports = Fluid\Fluid::getConfig('ports');

        $server = new Fluid\WebSockets\Server;
        $tasks = new Fluid\WebSockets\Tasks($server);
        $tasks->execute();

        new Fluid\WebSockets\Events($server);

        $loop = React\EventLoop\Factory::create();

        $root = $this;
        $loop->addPeriodicTimer(1, function() use ($root, $server, $tasks) {
            $root->renderStatus($server);
            $tasks->execute();
        });

        $loop->addPeriodicTimer(10, function() use ($root) {
            $root->upTimeCallback();
        });

        $context = new React\ZMQ\Context($loop);
        $pull = $context->getSocket(ZMQ::SOCKET_PULL);
        $pull->bind('tcp://127.0.0.1:' . $ports['zeromq']);
        $pull->on('message', array($tasks, 'message'));

        $socket = new React\Socket\Server($loop);
        $socket->listen($ports['websockets'], '0.0.0.0');

        new Ratchet\Server\IoServer(
            new Ratchet\WebSocket\WsServer(
                new Ratchet\Wamp\WampServer(
                    $server
                )
            ),
            $socket
        );

        $this->renderStatus($server);

        $loop->run();

        $this->release();
    }

    /**
     * Display daemon status
     *
     * @param   Fluid\WebSockets\Server $server
     * @return  void
     */
    private function renderStatus(Fluid\WebSockets\Server $server)
    {
        if ($this->displayStatus) {
            $status = $this->status;
            $status = str_replace('%uptime', $this->getReadableUpTime(), $status);
            $status = str_replace('%connections', count($server->getConnections()), $status);
            $status = str_replace('%memory', $this->getReadableMemoryUsage(), $status);
            $this->displayStatus($status);
        }
    }
}
