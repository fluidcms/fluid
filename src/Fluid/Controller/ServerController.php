<?php
namespace Fluid\Controller;

use Fluid\Controller;

class ServerController extends Controller
{
    public function status()
    {

        /*
        if (!empty(self::$request) && self::$method === 'POST' && strpos(self::$request, 'server') === 0 && isset(self::$input['session'])) {
            if (Session::validate(self::$input['session'])) {
                if (Daemon::isRunning()) {
                    // Daemon is already running
                    return json_encode(true);
                } else if (Daemon::runBackground()) {
                    // Start Daemon
                    return json_encode(true);
                } else {
                    // Could not start Daemon
                    return json_encode(false);
                }
            }
        }

        return false;*/
    }
}