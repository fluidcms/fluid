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
        $context = new ZMQContext();
        $socket = $context->getSocket(ZMQ::SOCKET_PUSH);
        $socket->connect("tcp://localhost:57586");
        $socket->send(json_encode($data));
    }
}