<?php
namespace Fluid\Socket;

use Ratchet\Wamp\WampServerInterface;
use Fluid;
use Fluid\Event;
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
 *
 * @package Fluid
 */
class WebSocketServer implements WampServerInterface
{
    const URI = '/fluidcms/websocket';

    /** @var WebSocketServer $server */
    private static $server;

    /** @var array $connections */
    protected $connections = array();

    /** @var int $startTime */
    private $startTime;

    /** @var bool $hadConnections */
    private $hadConnections = false;

    function __construct()
    {
        self::$server = $this;
        $this->startTime = time();
        Fluid\Socket\WebSocketServerEvents::register();
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
     * Alias of _subscribe
     *
     * @param string $userId
     * @param string $topic
     */
    public static function subscribe($userId, $topic)
    {
        self::$server->_subscribe($userId, $topic);
    }

    /**
     * Subscribe a user to a topic, users are subscribed based on their actions server side and do not subscribe to
     * topics client side
     *
     * @param string $userId
     * @param string $topic
     */
    public function _subscribe($userId, $topic)
    {
        foreach ($this->getConnections() as $sessionId => $connection) {
            if (isset($connection['user_id']) && $userId === $connection['user_id']) {
                $connection['topics'][] = $topic;
            }
        }
    }

    /**
     * @param ConnectionInterface|WampConnection $conn
     * @param Topic|string $topic
     */
    public function onSubscribe(ConnectionInterface $conn, $topic)
    {
        $data = json_decode($topic->getId(), true);

        if (!array_key_exists($topic->getId(), $this->connections[$conn->WAMP->sessionId])) {
            $this->connections[$conn->WAMP->sessionId] = array(
                'session' => $data['session'],
                'branch' => $data['branch'],
                'user_id' => $data['user_id'],
                'user_name' => $data['user_name'],
                'user_email' => $data['user_email'],
                'topics' => array()
            );
            Log::add("User {$data['user_id']} subscribed ({$data['user_name']} <{$data['user_email']}>)");
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
        unset($this->connections[$conn->WAMP->sessionId]);
    }

    /**
     * @param ConnectionInterface|WampConnection $conn
     */
    public function onOpen(ConnectionInterface $conn)
    {
        $this->hadConnections = true;
        $this->connections[$conn->WAMP->sessionId] = array();

        Log::add("User opened connection " . $conn->WAMP->sessionId);
        Event::trigger('websocket:connection:open', array('conn' => $conn));
    }

    /**
     * @param ConnectionInterface|WampConnection $conn
     */
    public function onClose(ConnectionInterface $conn)
    {
        if (isset($this->connections[$conn->WAMP->sessionId]) && is_array($this->connections[$conn->WAMP->sessionId])) {
            $topic = key($this->connections[$conn->WAMP->sessionId]);
            ServerEvents::unregister($this->connections[$conn->WAMP->sessionId][$topic]['user_id']);
        }
        unset($this->connections[$conn->WAMP->sessionId]);

        Log::add("User closed connection " . $conn->WAMP->sessionId);
        Event::trigger('websocket:connection:close', array('conn' => $conn));
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
            $data = json_decode($topic, true);

            Log::add("User {$data['user_id']} called method {$params['method']} {$params['url']}");

            ob_start();
            new WebSocketRequest(
                $params['url'],
                $params['method'],
                $params['data'],
                $data['branch'],
                array(
                    'id' => $data['user_id'],
                    'name' => $data['user_name'],
                    'email' => $data['user_email']
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
