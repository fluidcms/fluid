<?php

namespace Fluid\Daemons;

use Fluid, React, Ratchet, ZMQ, ZMQSocketException;

class WebSocket extends Fluid\Daemon implements Fluid\DaemonInterface
{
    protected $statusFile = 'WebSocketStatus.txt';

    /*
     * Run the web socket server
     *
     * @return  void
     */
    public function run()
    {
        $this->upTimeCallback();

        $server = new Fluid\WebSockets\Server;
        $tasks = new Fluid\WebSockets\Tasks($server);
        $tasks->execute();

        $loop = React\EventLoop\Factory::create();

        $root = $this;
        $loop->addPeriodicTimer(1, function() use ($root, $server, $tasks) {
            $root->renderStatus($server);
            $tasks->execute();
        });

        $loop->addPeriodicTimer(10, function() use ($root) {
            $root->upTimeCallback();
        });

        try {
            $context = new React\ZMQ\Context($loop);
            $pull = $context->getSocket(ZMQ::SOCKET_PULL);
            $pull->bind('tcp://127.0.0.1:57586');
            $pull->on('message', array($tasks, 'message'));
        } catch (ZMQSocketException $e) {
            return;
        }

        try {
            $socket = new React\Socket\Server($loop);
            $socket->listen(8180, '0.0.0.0');
        } catch(React\Socket\ConnectionException $e) {
            return;
        }

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
    }

    /**
     * Display daemon status
     *
     * @param   Fluid\WebSockets\Server $server
     * @return  void
     */
    private function renderStatus(Fluid\WebSockets\Server $server)
    {
        $status = $this->status;
        $status = str_replace('%uptime', $this->getReadableUpTime(), $status);
        $status = str_replace('%connections', count($server->getConnections()), $status);
        $status = str_replace('%memory', $this->getReadableMemoryUsage(), $status);
        $this->displayStatus($status);
    }
}
