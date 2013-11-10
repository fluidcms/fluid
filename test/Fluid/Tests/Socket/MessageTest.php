<?php

namespace Fluid\Tests\Socket;

use PHPUnit_Framework_TestCase;
use Fluid\Event;
use Fluid\Socket\Server;
use Fluid\Socket\Message;
use Fluid\Socket\Server\Message as MessageServer;

class MessageTest extends PHPUnit_Framework_TestCase
{
    public function testSendMessage()
    {
        $server = new Server();

        $loop = $server->getLoop();

        $server->add(new MessageServer(), MessageServer::URI);
        $server->create();

        $loop->addPeriodicTimer(2, function () use ($loop) {
            $loop->stop();
        });

        $result = array();
        Event::on('test', function ($val1, $val2) use (&$result, $loop) {
            $result['val1'] = $val1;
            $result['val2'] = $val2;
            $loop->stop();
        });

        $message = new Message();
        $message->setLoop($loop);
        $message->send('test', array('val1' => 'mytest', 'val2' => 'mytest2'));

        $server->run();

        $this->assertEquals('mytest', $result['val1']);
        $this->assertEquals('mytest2', $result['val2']);
    }
}