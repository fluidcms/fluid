<?php

namespace Fluid\WebSockets;

use Ratchet, Fluid;

class Server implements Ratchet\Wamp\WampServerInterface
{
    protected $connections = array();
    private $tasks;

    public function push($data)
    {}


    /*public function parse($data)
    {
        $data = json_decode($data, true);
        switch($data['task']) {
            case 'watchBranchStatus':
                WatchBranchStatus::parse($data, $this->connections);
                break;
        }
    }*/

    /**
     * Get active connections
     *
     * @return  array
     */
    public function getConnections() {
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
        $conn->callError($id, $topic, 'You cannot send messages to the server.')->close();
    }

    public function onPublish(Ratchet\ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible)
    {
        $conn->close();
    }

    public function onError(Ratchet\ConnectionInterface $conn, \Exception $e)
    {
        unset($this->connections[$conn->WAMP->sessionId]);
    }
}
