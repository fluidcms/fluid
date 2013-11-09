<?php

namespace Fluid;
use ZMQ;
use ZMQContext;
use Exception;

class MessageQueue
{
    private static $portFile = ".zmqport";
    private static $defaultPort = 57600;
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
     * Get an avalaible port for ZeroMQ
     *
     * @param   int $port
     * @param   int $loop
     * @throws  Exception
     * @return  int
     */
    public static function getAvalaiblePort($port = null, $loop = 0)
    {
        if (null === $port) {
            $port = self::$defaultPort;
        }

        if ($loop >= 100) {
            throw new Exception('Could not find a port to open ZeroMQ server');
        }

        $connection = @fsockopen('127.0.0.1', $port);

        if (is_resource($connection)) {
            fclose($connection);
            return self::getAvalaiblePort(($port+1), ($loop+1));
        }

        file_put_contents(Config::get('storage') . '/' . self::$portFile, json_encode(array("port" => $port)));
        return $port;
    }

    /**
     * Get ZeroMQ port
     *
     * @return  int
     */
    public static function getPort()
    {
        if (isset(self::$port)) {
            return self::$port;
        }

        if (is_file(Config::get('storage') . '/' . self::$portFile)) {
            $content = file_get_contents(Config::get('storage') . '/' . self::$portFile);
            $content = json_decode($content, true);

            if (isset($content['port'])) {
                self::$port = (int)$content['port'];
                return self::$port;
            }
        }

        return false;
    }
}