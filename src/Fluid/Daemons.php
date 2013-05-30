<?php

namespace Fluid;

class Daemons
{
    /**
     * Start Fluid daemons
     *
     * @return  void
     */
    public static function start()
    {
        Daemons\GearmanServer::run();
        Daemons\WebSocket::run();
    }
}