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
        $topidId = explode('/', $topic->getId());
        $branch = (isset($topidId[0]) ? $topidId[0] : null);
        $subject = 'all'; // TODO implement different subscriptions subjects (isset($topidId[1]) ? $topidId[1] : 'all');

        if (!array_key_exists($topic->getId(), $this->connections[$conn->WAMP->sessionId])) {
            $this->connections[$conn->WAMP->sessionId][$topic->getId()] = array(
                'user' => $topic,
                'branch' => $branch,
                'subject' => $subject
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
            ob_start();
            Fluid\ManagerRouter::route($params['url'], $params['method'], $params['data']);
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
