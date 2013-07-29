<?php

namespace Fluid;

use ZMQ, ZMQContext;

class MessageQueue
{
    /**
     * Send data to Web Socket Server
     *
     * @param   array  $data
     * @return  void
     */
    public static function send($data)
    {
        $ports = Fluid::getConfig('ports');

        $context = new ZMQContext();
        $socket = $context->getSocket(ZMQ::SOCKET_PUSH);
        $socket->connect("tcp://localhost:" . $ports['zeromq']);
        $socket->send(json_encode($data));
    }
}