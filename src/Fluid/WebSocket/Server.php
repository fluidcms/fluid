<?php

namespace Fluid\WebSocket;
use Ratchet;
use Fluid;
use Fluid\Debug\Log;
use Fluid\Requests\WebSocket as WebSocketRequest;
use Fluid\WebSocket\Events as ServerEvents;

class Server implements Ratchet\Wamp\WampServerInterface
{
    private $lastConnection;
    protected $connections = array();

    /**
     * Init websocket server
     */
    public function __construct()
    {
        $this->lastConnection = time();
        Log::add("Websocket Server Started\n                    ==============================");
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

        Log::add("User subscribe " . $topic->getId());

        if (!array_key_exists($topic->getId(), $this->connections[$conn->WAMP->sessionId])) {
            $this->connections[$conn->WAMP->sessionId][$topic->getId()] = array(
                'session' => $topicId['session'],
                'branch' => $topicId['branch'],
                'user_id' => $topicId['user_id'],
                'user_name' => $topicId['user_name'],
                'user_email' => $topicId['user_email'],
                'topic' => $topic
            );
            $topic->broadcast('true');
        }
    }

    public function onUnSubscribe(Ratchet\ConnectionInterface $conn, $topic)
    {
        Log::add("User unsubscribe");
        unset($this->connections[$conn->WAMP->sessionId][$topic->getId()]);
    }

    public function onOpen(Ratchet\ConnectionInterface $conn)
    {
        Log::add("User open connection");
        $this->connections[$conn->WAMP->sessionId] = array();
    }

    public function onClose(Ratchet\ConnectionInterface $conn)
    {
        Log::add("User close connection");
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
        Log::add("User call method");
        if (
            isset($params['url']) &&
            isset($params['method']) &&
            isset($params['data']) &&
            is_string($params['url']) &&
            is_string($params['method']) &&
            is_array($params['data'])
        ) {
            $topic = json_decode($topic, true);

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
        Log::add("User publish message");
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
}
