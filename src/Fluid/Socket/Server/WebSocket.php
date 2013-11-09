<?php

namespace Fluid\Socket\Server;

use Ratchet\Wamp\WampServerInterface;
use Fluid;
use Fluid\Debug\Log;
use Fluid\Requests\WebSocket as WebSocketRequest;
use Fluid\WebSocket\Events as ServerEvents;
use Ratchet;
use React;
use Ratchet\ConnectionInterface;

/**
 * WebSocket Server for receiving and sending communications to the local server, remote servers and clients
 * @package Fluid
 */
class WebSocket implements WampServerInterface
{
    const URI = '/fluidcms/websocket';

    /** @var array $connections */
    protected $connections = array();

    /**
     * Get active connections
     *
     * @return  array
     */
    public function getConnections()
    {
        return $this->connections;
    }

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
            Log::add("User " . $topicId['user_id'] ." subscribed");
            $topic->broadcast('true');
        }
    }

    public function onUnSubscribe(ConnectionInterface $conn, $topic)
    {
        Log::add("User unsubscribed");
        unset($this->connections[$conn->WAMP->sessionId][$topic->getId()]);
    }

    public function onOpen(ConnectionInterface $conn)
    {
        Log::add("User opened connection " . $conn->WAMP->sessionId);
        $this->connections[$conn->WAMP->sessionId] = array();
    }

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
        }

        else {
            $conn->callError($id, $topic, 'Invalid call')->close();
        }
    }

    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible)
    {
        Log::add("User published message");
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        Log::add("Error");
        if (is_array($this->connections[$conn->WAMP->sessionId])) {
            $topic = key($this->connections[$conn->WAMP->sessionId]);
            ServerEvents::unregister($this->connections[$conn->WAMP->sessionId][$topic]['user_id']);
        }
        unset($this->connections[$conn->WAMP->sessionId]);
    }
}
