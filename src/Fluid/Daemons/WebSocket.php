<?php

namespace Fluid\Daemons;

use Fluid, React, Ratchet, ZMQ;

class WebSocket
{
    /*
     * Run the websocket server
     *
     * @return  void
     */
    public static function run()
    {
        $server = new Fluid\WebSockets\Server;

        $loop = React\EventLoop\Factory::create();

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
}
