<?php

namespace Fluid\Tests\Socket;

use PHPUnit_Framework_TestCase;
use Fluid\Socket\Server;
use Fluid\Socket\Server\WebSocket as WebSocketServer;

class WebSocketTest extends PHPUnit_Framework_TestCase
{
    public function testSendMessage()
    {
        $server = new Server();

        $loop = $server->getLoop();

        $server->add(new WebSocketServer(), WebSocketServer::URI);
        $server->create();

        $loopWorking = false;
        $loop->addPeriodicTimer(0.001, function () use ($loop, &$loopWorking) {
            $loopWorking = true;
            $loop->stop();
        });

        $server->run();

        $this->assertTrue($loopWorking);
    }
}