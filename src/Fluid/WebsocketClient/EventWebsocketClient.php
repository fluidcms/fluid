<?php
namespace Fluid\WebsocketClient;

use Fluid\Logger;
use Fluid\WebsocketServer\EventWebsocketServer;
use WebSocketClient\Exception\ConnectionException;
use WebSocketClient\WebSocketClient;
use WebSocketClient\WebSocketClientInterface;
use React\EventLoop\Factory;
use Fluid\ConfigInterface;
use React\EventLoop\StreamSelectLoop;
use Psr\Log\LoggerInterface;

class EventWebsocketClient implements WebSocketClientInterface
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
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ConfigInterface $config
     * @param LoggerInterface|null $logger
     */
    public function __construct(ConfigInterface $config, LoggerInterface $logger = null)
    {
        $this->setConfig($config);
        if (null !== $logger) {
            $this->setLogger($logger);
        }
    }

    /**
     * @return $this
     */
    public function run()
    {
        $this->getLoop()->run();

        return $this;
    }

    /**
     * @param string $messageEvent
     * @param array $messageData
     * @return bool
     */
    public function send($messageEvent, array $messageData)
    {
        $this->setMessageEvent($messageEvent);
        $this->setMessageData($messageData);
        try {
            $this->setClient(new WebSocketClient(
                $this,
                $this->getLoop(),
                '127.0.0.1',
                $this->getConfig()->getWebsocketPort(),
                $this->getConfig()->getAdminPath() . EventWebsocketServer::URI
            ));
            $this->run();
            return true;
        } catch(ConnectionException $e) {
            return false;
        }
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
                $this->getLogger()->debug('Event websocket client successfully sent ' . $event);
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
    private function createLoop()
    {
        return $this->setLoop(Factory::create());
    }

    /**
     * @param ConfigInterface $config
     * @return $this
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @return ConfigInterface
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param LoggerInterface $logger
     * @return $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        if (null === $this->logger) {
            $this->createLogger();
        }
        return $this->logger;
    }

    /**
     * @return $this
     */
    private function createLogger()
    {
        return $this->setLogger(new Logger($this->getConfig()));
    }
}