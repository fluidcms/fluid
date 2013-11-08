<?php

namespace Fluid\Socket\Server;

use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampConnection;
use Ratchet\Wamp\WampServerInterface;
use Ratchet\Wamp\Topic;
use Ratchet\Server\IoConnection;
use Fluid\Event;

class Message implements WampServerInterface
{
    const URI = '/fluidcms/message';

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
            $conn->close();
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
        if (!$this->isAllowed($conn)) {
            $conn->close();
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
        }

        $event = json_decode($event, true);
        if (isset($event['event'], $event['data'])) {
            Event::trigger($event['event'], $event['data']);
        }
    }

    /**
     * @param ConnectionInterface|WampConnection $conn
     * @param Exception $e
     */
    public function onError(ConnectionInterface $conn, Exception $e)
    {
    }
}
