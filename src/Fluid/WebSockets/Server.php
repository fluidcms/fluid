<?php

namespace Fluid\WebSockets;

use Ratchet, Fluid;

class Server implements Ratchet\Wamp\WampServerInterface
{
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

    public function onSubscribe(Ratchet\ConnectionInterface $conn, $topic)
    {
        $topicId = json_decode($topic->getId(), true);

        if (!array_key_exists($topic->getId(), $this->connections[$conn->WAMP->sessionId])) {
            $this->connections[$conn->WAMP->sessionId][$topic->getId()] = array(
                'branch' => $topicId['branch'],
                'user_id' => $topicId['user_id'],
                'user_name' => $topicId['user_name'],
                'user_email' => $topicId['user_email'],
                'topic' => $topic
            );
        }
    }

    public function onUnSubscribe(Ratchet\ConnectionInterface $conn, $topic)
    {
        unset($this->connections[$conn->WAMP->sessionId][$topic->getId()]);
    }

    public function onOpen(Ratchet\ConnectionInterface $conn)
    {
        $this->connections[$conn->WAMP->sessionId] = array();
    }

    public function onClose(Ratchet\ConnectionInterface $conn)
    {
        unset($this->connections[$conn->WAMP->sessionId]);
    }

    public function onCall(Ratchet\ConnectionInterface $conn, $id, $topic, array $params)
    {
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
            new Requests(
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

            $conn->send('[3,"' . $id . '",' . $retval . ']');
        }

        else {
            $conn->callError($id, $topic, 'Invalid call')->close();
        }
    }

    public function onPublish(Ratchet\ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible)
    {
    }

    public function onError(Ratchet\ConnectionInterface $conn, \Exception $e)
    {
        unset($this->connections[$conn->WAMP->sessionId]);
    }
}
