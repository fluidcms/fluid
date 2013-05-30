<?php

namespace Fluid\Tasks;

use ZMQ, ZMQContext, React, Fluid\Fluid, Fluid\Git;

class WatchBranchStatus
{
    /*
     * Check the status of a branch until it is changed and send an alert to the user
     *
     * @param   string  $branch
     * @param   string  $user
     * @param   int     $lastTime
     * @return  void
     */
    public static function execute($branch, $user, $wait = false)
    {
        $context = new ZMQContext();

        $socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'fluid');
        $socket->connect("tcp://localhost:57586");

        $data = array('task' => 'watchBranchStatus', 'user' => $user, 'branch' => $branch, 'message' => '');

        $status = Git::status($branch);

        if (preg_match("/branch is behind '(.*)' by (\d*)/", $status, $match)) {
            $data['message'] = json_encode(array('target' => 'version', 'data' => array('status' => 'behind', 'amount' => $match[2])));
        } else if (strpos($status, "nothing to commit") === false) {
            $data['message'] = json_encode(array('target' => 'version', 'data' => array('status' => 'ahead')));
        } else {
            $data['message'] = json_encode(array('target' => 'version', 'data' => array('status' => 'nothing')));
        }

        if ($wait) {
            sleep(1); // TODO find a way to send the task each 1 second without holding up the script
        }

        $socket->send(json_encode($data));
    }
}