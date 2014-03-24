<?php
namespace Fluid\Controller;

use Fluid\Controller;
use Fluid\Helper\SessionHelper;
use Fluid\Response;
use Fluid\User\UserEntity;

class ServerController extends Controller
{
    use SessionHelper;

    public function status()
    {
        $user = $this->getUser();
        if ($user instanceof UserEntity) {
            die('connected');
            return;
        }
        $this->response->code(Response::RESPONSE_CODE_FORBIDDEN);
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