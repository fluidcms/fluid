<?php
namespace Fluid\Socket;

use WebSocketClient;
use WebSocketClient\WebSocketClientInterface;
use React\EventLoop\Factory;
use Fluid\Config;
use React\EventLoop\StreamSelectLoop;
use Fluid\Debug\Log;

/**
 * Class Message
 * @package Fluid\WebSocket
 */
class Message implements WebSocketClientInterface
{
    /** @var string $event */
    private $event;

    /** @var array $data */
    private $data;

    /** @var WebSocketClient $client */
    private $client;

    /** @var bool $run */
    private $run = false;

    /** @var StreamSelectLoop $loop */
    private $loop;

    /**
     * @param string $event
     * @param array $data
     */
    public function send($event, array $data)
    {
        $port = Config::get('websocket');

        if (!$this->run) {
            $this->setLoop(Factory::create());
            $this->run = false;
        }

        $this->setEvent($event)->setData($data);

        $this->setClient(new WebSocketClient(
            $this,
            $this->getLoop(),
            '127.0.0.1',
            $port,
            MessageServer::URI
        ));

        if (!$this->run) {
            $this->getLoop()->run();
        }
    }

    /**
     * @param array $data
     */
    public function onWelcome(array $data)
    {
        $loop = $this->getLoop();
        $event = $this->getEvent();

        $this->client->call(
            'message',
            array($this->getEvent(), $this->getData()),
            function() use ($loop, $event) {
                Log::add('Message client successfully sent event ' . $event);
                $loop->stop();
            }
        );

        if (!$this->run) {
            // Timeout after 10 seconds
            $loop->addPeriodicTimer(10, function () use ($loop) {
                $loop->stop();
            });
        }
    }

    /**
     * @param string $topic
     * @param string $message
     */
    public function onEvent($topic, $message)
    {
    }

    /**
     * @param WebSocketClient $client
     */
    public function setClient(WebSocketClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $event
     * @return self
     */
    public function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return string
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param array $data
     * @return self
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param StreamSelectLoop $loop
     * @return self
     */
    public function setLoop($loop)
    {
        $this->run = true;
        $this->loop = $loop;
        return $this;
    }

    /**
     * @return StreamSelectLoop
     */
    public function getLoop()
    {
        return $this->loop;
    }
}
