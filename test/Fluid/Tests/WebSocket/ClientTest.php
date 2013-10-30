<?php

namespace Fluid\Tests\WebSocket;
use PHPUnit_Framework_TestCase;
use Fluid\WebSocket\Server;
use Fluid\WebSocket\Client;
use React;

class ClientTest extends PHPUnit_Framework_TestCase
{
    public function testClient()
    {
        $loop = React\EventLoop\Factory::create();

        // Server
        $server = new Server($loop);

        // Client
        $client = new Client($loop);
        $client->connect();

        $loop->addPeriodicTimer(1, function () use ($loop) {
            $loop->stop();
        });

        $loop->run();

        return;

        //$client->connect("localhost", 34344, "/fluidcms/", "this");
        $client->authenticate();


        $client = stream_socket_client('tcp://127.0.0.1:34344');
        $conn = new React\Socket\Connection($client, $loop);
        $conn->pipe(new React\Stream\Stream(STDOUT, $loop));
        $conn->write("Hello World!\n");

        $loop->addPeriodicTimer(1, function () use ($socket) {
            $socket->shutdown();
        });

        $loop->run();

        return;






        /*        $client = new Client();
                $client->connect("localhost", 45543, "/fluidcms/", "this");

                $loop = React\EventLoop\Factory::create();

                $socket = fsockopen($host, $port, $errno, $errstr, 2);
                socket_set_timeout($socket, 0, 10000);

                $loop->addWriteStream($socket, function() {
                    echo 'do something';
                });

                $socket = new React\Socket\Server($loop);
                $socket->listen($port, '0.0.0.0');

                new Ratchet\Server\IoServer(
                    new Ratchet\WebSocket\WsServer(
                        new Ratchet\Wamp\WampServer(
                            $server
                        )
                    ),
                    $socket
                );


                $loop->run();

                echo '';*/
    }
}