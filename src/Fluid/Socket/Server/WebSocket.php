<?php

namespace Fluid\Socket\Server;

use Ratchet\Wamp\WampServerInterface;
use Fluid;
use Fluid\Debug\Log;
use Fluid\Requests\WebSocket as WebSocketRequest;
use Fluid\Socket\Events as ServerEvents;
use Ratchet;
use React;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampConnection;
use Ratchet\Wamp\Topic;
use Exception;

/**
 * WebSocket Server for receiving and sending communications to the local server, remote servers and clients
 * @package Fluid
 */
class WebSocket implements WampServerInterface
{
    const URI = '/fluidcms/websocket';

    /** @var array $connections */
    protected $connections = array();

    /** @var int $startTime */
    private $startTime;

    /** @var bool $hadConnections */
    private $hadConnections = false;

    function __construct()
    {
        $this->startTime = time();
    }

    /**
     * Get active connections
     *
     * @return  array
     */
    public function getConnections()
    {
        return $this->connections;
    }

    /**
     * Determin if the server is inactive
     *
     * @return bool
     */
    public function isInactive()
    {
        if ($this->countConnections()) {
            return false;
        }

        if ($this->hadConnections) {
            return true;
        }

        if ($this->startTime < time() - 30) { // Inactive for 30 seconds
            return true;
        }

        return false;
    }

    /**
     * @return int
     */
    public function countConnections()
    {
        return count($this->connections);
    }

    /**
     * @param ConnectionInterface|WampConnection $conn
     * @param Topic|string $topic
     */
    public function onSubscribe(ConnectionInterface $conn, $topic)
    {
        $topicId = json_decode($topic->getId(), true);

        if (!array_key_exists($topic->getId(), $this->connections[$conn->WAMP->sessionId])) {
            $this->connections[$conn->WAMP->sessionId][$topic->getId()] = array(
                'session' => $topicId['session'],
                'branch' => $topicId['branch'],
                'user_id' => $topicId['user_id'],
                'user_name' => $topicId['user_name'],
                'user_email' => $topicId['user_email'],
                'topic' => $topic
            );
            Log::add("User " . $topicId['user_id'] . " subscribed");
            $topic->broadcast('true');
        }
    }

    /**
     * @param ConnectionInterface|WampConnection $conn
     * @param Topic|string $topic
     */
    public function onUnSubscribe(ConnectionInterface $conn, $topic)
    {
        Log::add("User unsubscribed");
        unset($this->connections[$conn->WAMP->sessionId][$topic->getId()]);
    }

    /**
     * @param ConnectionInterface|WampConnection $conn
     */
    public function onOpen(ConnectionInterface $conn)
    {
        $this->hadConnections = true;
        Log::add("User opened connection " . $conn->WAMP->sessionId);
        $this->connections[$conn->WAMP->sessionId] = array();
    }

    /**
     * @param ConnectionInterface|WampConnection $conn
     */
    public function onClose(ConnectionInterface $conn)
    {
        Log::add("User closed connection " . $conn->WAMP->sessionId);

        if (isset($this->connections[$conn->WAMP->sessionId]) && is_array($this->connections[$conn->WAMP->sessionId])) {
            $topic = key($this->connections[$conn->WAMP->sessionId]);
            ServerEvents::unregister($this->connections[$conn->WAMP->sessionId][$topic]['user_id']);
        }
        unset($this->connections[$conn->WAMP->sessionId]);

        // Shut down server if no one is connected
        if (count($this->connections) == 0) {
            exit;
        }
    }

    /**
     * @param ConnectionInterface|WampConnection $conn
     * @param string $id
     * @param Topic|string $topic
     * @param array $params
     */
    public function onCall(ConnectionInterface $conn, $id, $topic, array $params)
    {
        // Ping
        if (isset($params['ping'])) {
            $conn->send('true');
            return;
        }

        if (
            isset($params['url']) &&
            isset($params['method']) &&
            isset($params['data']) &&
            is_string($params['url']) &&
            is_string($params['method']) &&
            is_array($params['data'])
        ) {
            $topic = json_decode($topic, true);

            Log::add("User " . $topic['user_id'] . " called method " . $params['method'] . " " . $params['url']);

            ob_start();
            new WebSocketRequest(
                $params['url'],
                $params['method'],
                $params['data'],
                $topic['branch'],
                array(
                    'id' => $topic['user_id'],
                    'name' => $topic['user_name'],
                    'email' => $topic['user_email']
                )
            );
            $retval = ob_get_contents();
            ob_end_clean();

            if (!empty($retval)) {
                $conn->send('[3,"' . $id . '",' . $retval . ']');
            }
        } else {
            $conn->callError($id, $topic, 'Invalid call')->close();
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
        Log::add("User published message");
    }

    /**
     * @param ConnectionInterface|WampConnection $conn
     * @param Exception $e
     */
    public function onError(ConnectionInterface $conn, Exception $e)
    {
        Log::add("Error");
        if (is_array($this->connections[$conn->WAMP->sessionId])) {
            $topic = key($this->connections[$conn->WAMP->sessionId]);
            ServerEvents::unregister($this->connections[$conn->WAMP->sessionId][$topic]['user_id']);
        }
        unset($this->connections[$conn->WAMP->sessionId]);
    }
}
