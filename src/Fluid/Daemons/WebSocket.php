<?php

namespace Fluid\Daemons;

use Fluid, React, Ratchet, ZMQ;

class WebSocket
{
    private $status;
    private $lines = 0;
    private $timeStart;

    /*
     * Run the web socket server
     *
     * @return  void
     */
    public static function run()
    {
        $daemon = new self;
        $daemon->status = file_get_contents(__DIR__ . '/WebSocketStatus.txt');
        $daemon->timeStart = time();

        $server = new Fluid\WebSockets\Server;
        $tasks = new Fluid\WebSockets\Tasks($server);
        $tasks->execute();

        $daemon->displayStatus($server, $tasks);

        $loop = React\EventLoop\Factory::create();

        $loop->addPeriodicTimer(1, function() use ($daemon, $server, $tasks) {
            $daemon->displayStatus($server, $tasks);
            $tasks->execute();
        });

        $context = new React\ZMQ\Context($loop);

        $pull = $context->getSocket(ZMQ::SOCKET_PULL);

        $pull->bind('tcp://127.0.0.1:57586');
        $pull->on('message', array($server, 'parse'));

        $socket = new React\Socket\Server($loop);
        $socket->listen(8180, '0.0.0.0');

        new Ratchet\Server\IoServer(
            new Ratchet\WebSocket\WsServer(
                new Ratchet\Wamp\WampServer(
                    $server
                )
            ),
            $socket
        );

        $loop->run();
    }

    /**
     * Display daemon status
     *
     * @param   Fluid\WebSockets\Server $server
     * @param   Fluid\WebSockets\Tasks  $tasks
     * @return  void
     */
    public function displayStatus(Fluid\WebSockets\Server $server, Fluid\WebSockets\Tasks $tasks)
    {
        $clear = '';
        for($i = 0; $i < $this->lines; $i++) {
            $clear .= '\033[A';
        }

        passthru('printf "'.$clear.'"');

        $status = $this->status;
        $status = str_replace('%uptime', time() - $this->timeStart, $status);
        $status = str_replace('%connections', count($server->getConnections()), $status);
        passthru('echo "'.$status.'"');

        $lines = explode(PHP_EOL, $status);
        $this->lines = count($lines);
    }
}
