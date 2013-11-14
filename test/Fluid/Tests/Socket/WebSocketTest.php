<?php

namespace Fluid\Tests\Socket;

use PHPUnit_Framework_TestCase;
use Fluid\Daemon\Server;
use Fluid\Socket\WebSocketServer;

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