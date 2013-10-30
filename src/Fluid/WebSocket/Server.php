<?php

namespace Fluid\WebSocket;

use Ratchet\Wamp\WampServerInterface;
use Fluid;
use Fluid\Config;
use Fluid\Debug\Log;
use Fluid\Requests\WebSocket as WebSocketRequest;
use Fluid\WebSocket\Events as ServerEvents;
use Ratchet;
use React;
use React\EventLoop\StreamSelectLoop;

/**
 * WebSocket Server for receiving and sending communications to the local server, remote servers and clients
 * @package Fluid
 */
class Server implements WampServerInterface
{
    /** @var int $lastConnection Tiem of the last connedction */
    private $lastConnection;

    /** @var int $port */
    private $port;

    /** @var React\Socket\Server $socket */
    private $socket;

    /** @var array $connections */
    protected $connections = array();

    /**
     * Init websocket server
     */
    public function __construct(StreamSelectLoop $loop)
    {
        $this->lastConnection = time();

        Log::line();
        Log::add("Websocket Server Started");

        $this
            ->setPort(Config::get('websocket'))
            ->setSocket(new React\Socket\Server($loop));

        $this->socket->listen($this->port, '0.0.0.0');

        new Ratchet\Server\IoServer(
            new Ratchet\WebSocket\WsServer(
                new Ratchet\Wamp\WampServer(
                    $this
                )
            ),
            $this->socket
        );
    }

    /**
     * Releasing websocket server
     */
    public function __destruct()
    {
        Log::add("Websocket Server Closed");
    }

    /**
     * Determine if the server is inactive
     *
     * @return  int
     */
    public function isInactive()
    {
        if (count($this->connections) === 0 && $this->lastConnection + 1 < time()) {
            return true;
        }
        return false;
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

    public function onSubscribe(Ratchet\ConnectionInterface $conn, $topic)
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
            Log::add("User " . $topicId['user_id'] ." subscribed");
            $topic->broadcast('true');
        }
    }

    public function onUnSubscribe(Ratchet\ConnectionInterface $conn, $topic)
    {
        Log::add("User unsubscribed");
        unset($this->connections[$conn->WAMP->sessionId][$topic->getId()]);
    }

    public function onOpen(Ratchet\ConnectionInterface $conn)
    {
        Log::add("User opened connection " . $conn->WAMP->sessionId);
        $this->connections[$conn->WAMP->sessionId] = array();
    }

    public function onClose(Ratchet\ConnectionInterface $conn)
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

    public function onCall(Ratchet\ConnectionInterface $conn, $id, $topic, array $params)
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
        }

        else {
            $conn->callError($id, $topic, 'Invalid call')->close();
        }
    }

    public function onPublish(Ratchet\ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible)
    {
        Log::add("User published message");
    }

    public function onError(Ratchet\ConnectionInterface $conn, \Exception $e)
    {
        Log::add("Error");
        if (is_array($this->connections[$conn->WAMP->sessionId])) {
            $topic = key($this->connections[$conn->WAMP->sessionId]);
            ServerEvents::unregister($this->connections[$conn->WAMP->sessionId][$topic]['user_id']);
        }
        unset($this->connections[$conn->WAMP->sessionId]);
    }

    /**
     * @param int $port
     * @return self
     */
    public function setPort($port)
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param \React\Socket\Server $socket
     * @return self
     */
    public function setSocket($socket)
    {
        $this->socket = $socket;
        return $this;
    }

    /**
     * @return \React\Socket\Server
     */
    public function getSocket()
    {
        return $this->socket;
    }

}
