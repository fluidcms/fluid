<?php

namespace Fluid\WebSocket;

use Fluid\Event as FluidEvent;
use Fluid\Requests\WebSocket as WebSocketRequest;

class Events
{
    private static $self;

    private $server;
    private $users = array();

    /**
     * Bind events to server
     *
     * @param Server $server
     */
    public function __construct(Server $server)
    {
        self::$self = $this;
        $this->server = $server;
        $this->onHistoryChange();
        $this->onMapChange();
    }

    /**
     * Register a user to events
     *
     * @param string $user
     * @param string $topic
     */
    public static function register($user, $topic)
    {
        if (self::$self instanceof Events) {
            self::$self->registerUser($user, $topic);
        }
    }

    /**
     * Register a user to events
     *
     * @param string $user
     */
    public static function unregister($user)
    {
        if (self::$self instanceof Events) {
            self::$self->unregisterUser($user);
        }
    }

    /**
     * Register a user to events
     *
     * @param string $user
     * @param string $topic
     */
    public function registerUser($user, $topic)
    {
        if (!isset($this->users[$user])) {
            $this->users[$user] = array();
        }

        $this->users[$user][$topic] = true;
    }

    /**
     * Unregister a user from events
     *
     * @param string $user
     */
    public function unregisterUser($user)
    {
        unset($this->users[$user]);
    }

    /**
     * Send a message to the user
     *
     * @param string $user
     * @param string $message
     */
    public function sendMessage($user, $message)
    {
        foreach ($this->server->getConnections() as $connection => $subscribers) {
            foreach ($subscribers as $subscriber) {
                if ($subscriber['user_id'] == $user) {
                    $subscriber['topic']->broadcast($message);
                }
            }
        }

    }

    /**
     * Checks if a topic is registered
     *
     * @param string $topic
     * @return bool
     */
    private function topicIsRegistered($topic)
    {
        foreach ($this->users as $user) {
            if (isset($user[$topic])) {
                return true;
            }
        }
        return false;
    }

    /**
     * History change event
     */
    private function onHistoryChange()
    {
        $root = $this;
        FluidEvent::on('historyChange', function ($branch) use ($root) {
            // Send new map to user
            $root->refreshMap($branch);
        });
    }

    /**
     * History change event
     */
    private function onMapChange()
    {
        $root = $this;
        FluidEvent::on('mapChange', function ($branch) use ($root) {
            // Send new map to user
            $root->refreshMap($branch);
        });
    }

    /**
     * History change event
     *
     * @param string $branch
     */
    public function refreshMap($branch)
    {
        if ($this->topicIsRegistered('map')) {

            ob_start();
            new WebSocketRequest('map', 'GET', array(), $branch);
            $retval = json_decode(ob_get_contents());
            ob_end_clean();

            $message = array(
                'target' => 'map',
                'data' => $retval
            );

            foreach ($this->users as $user => $topics) {
                if (isset($topics['map'])) {
                    $this->sendMessage($user, json_encode($message));
                }
            }
        }
    }
}