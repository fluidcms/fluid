<?php

namespace Fluid\Tasks;

use ZMQ, ZMQContext, React, Fluid\Fluid;

class WatchBranchStatus
{
    /*
     * Check the status of a branch until it is changed and send an alert to the user
     *
     * @param   string  $branch
     * @param   string  $user
     * @param   int     $lastTime
     */
    public function __construct($branch, $user, $wait = false)
    {
        $context = new ZMQContext();

        $socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'fluid');
        $socket->connect("tcp://localhost:57586");

        $data = array('task' => 'watchBranchStatus', 'user' => $user, 'branch' => $branch, 'message' => '');

        chdir(Fluid::getConfig('storage') . $branch);
        $retval = exec("git status", $retval);

        if (strpos($retval, "nothing to commit") === false) {
            $data['message'] = 'ready to publish';
        } else {
            $data['message'] = 'not ready to publish';
        }

        if ($wait) {
            sleep(1); // TODO find a way to send the task each 1 second without holding up the script
        }

        $socket->send(json_encode($data));

        return true;
    }

    public static function run($branch, $user, $wait = false)
    {
        return new self($branch, $user, $wait);
    }
}