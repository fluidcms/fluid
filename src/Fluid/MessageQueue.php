<?php

namespace Fluid;

use ZMQ, ZMQContext;

class MessageQueue
{
    private static $port;

    /**
     * Send data to Web Socket Server
     *
     * @param   array  $data
     * @return  void
     */
    public static function send($data)
    {
        if ($port = self::getPort()) {
            $context = new ZMQContext();
            $socket = $context->getSocket(ZMQ::SOCKET_PUSH);
            $socket->connect("tcp://localhost:{$port}");
            $socket->send(json_encode($data));
        }
    }

    /**
     * Get ZeroMQ port
     *
     * @return  int
     */
    private static function getPort()
    {
        if (isset(self::$port)) {
            return self::$port;
        }

        if (is_file(Fluid::getConfig('storage') . ".data/zmqport")) {
            $content = file_get_contents(Fluid::getConfig('storage') . ".data/zmqport");
            $content = json_decode($content, true);

            if (isset($content['port'])) {
                self::$port = (int)$content['port'];
                return self::$port;
            }
        }

        return false;
    }
}