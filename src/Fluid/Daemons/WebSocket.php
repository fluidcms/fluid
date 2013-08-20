<?php

namespace Fluid\Daemons;

use Fluid,
    Fluid\WebSockets\Tasks,
    Fluid\WebSockets\Server,
    Fluid\WebSockets\Events as ServerEvents,
    Exception,
    React,
    Ratchet,
    ZMQ,
    ZMQSocketException,
    React\EventLoop\LibEventLoop;

class WebSocket extends Fluid\Daemon implements Fluid\DaemonInterface
{
    private $instanceId;
    private $lock;
    private $zmqPort = 57600;
    protected $statusFile = 'WebSocketStatus.txt';

    /**
     * Create and lock the lock file
     *
     * @return  mixed
     */
    private function lock()
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
    private function release()
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

        $server = new Server;
        $tasks = new Tasks($server);
        $tasks->execute();

        new ServerEvents($server);

        $loop = React\EventLoop\Factory::create();

        $root = $this;
        $loop->addPeriodicTimer(1, function() use ($root, $server, $tasks) {
            $root->renderStatus($server);
            $tasks->execute();
        });

        $loop->addPeriodicTimer(10, function() use ($root) {
            $root->upTimeCallback();
        });

        $this->zmqServer($loop, $tasks);

        $this->websocketServer($loop, $server);

        $this->renderStatus($server);

        $loop->run();

        $this->release();
    }

    /**
     * Get an avalaible port for ZeroMQ
     *
     * @param   int $port
     * @param   int $loop
     * @throws  Exception
     * @return  int
     */
    private function getPort($port, $loop = 0)
    {
        if ($loop >= 100) {
            throw new Exception('Could not find a port to open ZeroMQ server');
        }

        $connection = @fsockopen('127.0.0.1', $port);

        if (is_resource($connection)) {
            fclose($connection);
            return $this->getPort(($port+1), ($loop+1));
        }

        return $port;
    }

    /**
     * Create the ZeroMQ server
     *
     * @param   LibEventLoop    $loop
     * @param   Tasks   $tasks
     * @return  void
     */
    private function zmqServer(LibEventLoop $loop, Tasks $tasks)
    {
        // Get a port
        $port = $this->getPort($this->zmqPort);

        $context = new React\ZMQ\Context($loop);
        $pull = $context->getSocket(ZMQ::SOCKET_PULL);
        $pull->bind("tcp://127.0.0.1:{$port}");
        $pull->on('message', array($tasks, 'message'));

        file_put_contents(Fluid\Fluid::getConfig('storage') . ".data/zmqport", json_encode(array("id" => $this->instanceId, "port" => $port)));
    }

    /**
     * Create the WebSocket server
     *
     * @param   LibEventLoop    $loop
     * @param   Server  $server
     * @return  void
     */
    private function websocketServer(LibEventLoop $loop, Server $server)
    {
        $ports = Fluid\Fluid::getConfig('ports');

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
    }

    /**
     * Display daemon status
     *
     * @param   Server $server
     * @return  void
     */
    private function renderStatus(Server $server)
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
