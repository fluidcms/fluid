<?php
namespace Fluid\WebsocketServer;

use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampConnection;
use Ratchet\Wamp\WampServerInterface;
use Ratchet\Wamp\Topic;
use Ratchet\Server\IoConnection;
use Fluid\ConfigInterface;
use Fluid\Event;
use Psr\Log\LoggerInterface;

class EventWebsocketServer implements WampServerInterface
{
    const URI = 'event';

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Event
     */
    private $event;

    /**
     * @param ConfigInterface $config
     * @param LoggerInterface $logger
     * @param Event $event
     */
    public function __construct(ConfigInterface $config, LoggerInterface $logger, Event $event)
    {
        $this->setConfig($config);
        $this->setLogger($logger);
        $this->setEvent($event);
    }

    /**
     * Check if incoming connection is allowed, for message system, only localhost connections are allowed
     *
     * @param WampConnection $conn
     * @return true
     */
    private function isAllowed(WampConnection $conn)
    {
        if ($conn->__isset('wrappedConn')) {
            $incoming = $conn->__get('wrappedConn');
            if ($incoming instanceof IoConnection) {
                if (isset($incoming->remoteAddress)) {
                    $remoteAddress = $incoming->remoteAddress;
                    if ($remoteAddress === '127.0.0.1') {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * @param ConnectionInterface|WampConnection $conn
     * @param Topic|string $topic
     */
    public function onSubscribe(ConnectionInterface $conn, $topic)
    {
        if (!$this->isAllowed($conn)) {
            $conn->close();
            return;
        }
    }

    /**
     * @param ConnectionInterface|WampConnection $conn
     * @param Topic|string $topic
     */
    public function onUnSubscribe(ConnectionInterface $conn, $topic)
    {
    }

    /**
     * @param ConnectionInterface|WampConnection $conn
     */
    public function onOpen(ConnectionInterface $conn)
    {
        if (!$this->isAllowed($conn)) {
            $this->getLogger()->debug('Connection to message socket server was declined');
            $conn->close();
            return;
        }
    }

    /**
     * @param ConnectionInterface|WampConnection $conn
     */
    public function onClose(ConnectionInterface $conn)
    {
    }

    /**
     * @param ConnectionInterface|WampConnection $conn
     * @param string $id
     * @param Topic|string $topic
     * @param array $params
     */
    public function onCall(ConnectionInterface $conn, $id, $topic, array $params)
    {
        if (!$this->isAllowed($conn) || $topic->getId() !== 'message') {
            $conn->close();
            return;
        }

        if (isset($params[0]) && is_array($params[1])) {
            $event = $params[0];
            $args = $params[1];

            $conn->send(json_encode([
                3,
                $id,
                ['true']
            ]));

            $this->getEvent()->trigger($event, $args);
            $this->getLogger()->debug('Message socket received event ' . $event);
        }
    }

    /**
     * @param ConnectionInterface|WampConnection $conn
     * @param Topic|string $topic
     * @param string $event
     * @param array $exclude
     * @param array $eligible
     */
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible)
    {
        if (!$this->isAllowed($conn) || $topic->getId() !== 'message') {
            $conn->close();
            return;
        }
    }

    /**
     * @param ConnectionInterface|WampConnection $conn
     * @param Exception $e
     */
    public function onError(ConnectionInterface $conn, Exception $e)
    {
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
        return $this->logger;
    }

    /**
     * @param Event $event
     * @return $this
     */
    public function setEvent(Event $event)
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }
}