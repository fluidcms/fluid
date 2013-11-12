<?php

namespace Fluid\Socket\Server;

use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampConnection;
use Ratchet\Wamp\WampServerInterface;
use Ratchet\Wamp\Topic;
use Ratchet\Server\IoConnection;
use Fluid\Event;
use Fluid\Debug\Log;

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
        Log::add('Message socket subscribe');

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
        Log::add('Message socket unsubscribe');
    }

    /**
     * @param ConnectionInterface|WampConnection $conn
     */
    public function onOpen(ConnectionInterface $conn)
    {
        Log::add('Message socket connection');

        if (!$this->isAllowed($conn)) {
            Log::add('Connection to message socket server was declined');
            $conn->close();
            return;
        }
    }

    /**
     * @param ConnectionInterface|WampConnection $conn
     */
    public function onClose(ConnectionInterface $conn)
    {
        Log::add('Message socket close');
    }

    /**
     * @param ConnectionInterface|WampConnection $conn
     * @param string $id
     * @param Topic|string $topic
     * @param array $params
     */
    public function onCall(ConnectionInterface $conn, $id, $topic, array $params)
    {
        Log::add('Message socket call');

        if (!$this->isAllowed($conn) || $topic->getId() !== 'message') {
            $conn->close();
            return;
        }

        if (isset($params[0]) && is_array($params[1])) {
            $event = $params[0];
            $args = $params[1];

            $conn->send(json_encode(array(
                3,
                $id,
                array('true')
            )));

            Event::trigger($event, $args);
            Log::add('Message socket received event ' . $event);
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
        Log::add('Message socket publish');

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
        Log::add('Message socket error');
    }
}
