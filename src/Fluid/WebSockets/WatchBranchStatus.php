<?php

namespace Fluid\WebSockets;

use Fluid;

class WatchBranchStatus
{
    private static $lastStatuses = array();

    public static function parse($response, $connections)
    {
        foreach ($connections as $connection => $subscribers) {
            foreach ($subscribers as $subscriber) {
                if (strpos($subscriber->getId(), $response['branch']) === 0) {

                    if (!empty($response['message']) && (!isset(self::$lastStatuses[$connection]) || self::$lastStatuses[$connection] !== $response['message'])) {
                        $subscriber->broadcast($response['message']);
                    }

                    self::$lastStatuses[$connection] = $response['message'];
                    Fluid\Task::execute('WatchBranchStatus', array($response['branch'], $response['user'], true));

                }
            }
        }
    }
}