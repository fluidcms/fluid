<?php
namespace Fluid\Socket;

use Fluid\WebsocketServer\MessageWebsocketServer;
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
    /**
     * @var string
     */
    private $messageEvent;

    /**
     * @var array
     */
    private $messageData;

    /**
     * @var WebSocketClient
     */
    private $client;

    /**
     * @var bool
     */
    private $run = false;

    /**
     * @var StreamSelectLoop
     */
    private $loop;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->setConfig($config);
    }

    /**
     * @return $this
     */
    public function run()
    {
        if (!$this->loop) {
            $this->getLoop()->run();
        }

        return $this;
    }

    /**
     * @param string $messageEvent
     * @param array $messageData
     * @return $this
     */
    public function send($messageEvent, array $messageData)
    {
        $this->setMessageEvent($messageEvent);
        $this->setMessageData($messageData);
        $this->setClient(new WebSocketClient(
            $this,
            $this->getLoop(),
            '127.0.0.1',
            $this->getConfig()->getWebsocketPort(),
            $this->getConfig()->getAdminPath() . MessageWebsocketServer::URI
        ));
        return $this->run();
    }

    /**
     * @param array $data
     */
    public function onWelcome(array $data)
    {
        $loop = $this->getLoop();
        $event = $this->getMessageEvent();

        $this->client->call(
            'message',
            [$this->getMessageEvent(), $this->getMessageData()],
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
     * @return self
     */
    public function setClient(WebSocketClient $client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @param string $messageEvent
     * @return $this
     */
    public function setMessageEvent($messageEvent)
    {
        $this->messageEvent = $messageEvent;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessageEvent()
    {
        return $this->messageEvent;
    }

    /**
     * @param array $messageData
     * @return $this
     */
    public function setMessageData(array $messageData)
    {
        $this->messageData = $messageData;
        return $this;
    }

    /**
     * @return array
     */
    public function getMessageData()
    {
        return $this->messageData;
    }

    /**
     * @param StreamSelectLoop $loop
     * @return $this
     */
    public function setLoop($loop)
    {
        $this->loop = $loop;
        return $this;
    }

    /**
     * @return StreamSelectLoop
     */
    public function getLoop()
    {
        if (null === $this->loop) {
            $this->createLoop();
        }
        return $this->loop;
    }

    /**
     * @return $this
     */
    public function createLoop()
    {
        return $this->setLoop(Factory::create());
    }

    /**
     * @param Config $config
     * @return $this
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }
}