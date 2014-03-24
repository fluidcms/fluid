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
            if ($this->getDaemon()->isRunning()) {
                $this->getResponse()->setJson(true);
                return;
            } elseif ($this->getDaemon()->runBackground()) {
                $this->getResponse()->setJson(true);
                return;
            } else {
                $this->getResponse()->setJson(false);
                return;
            }
        }
        $this->getResponse()->setCode(Response::RESPONSE_CODE_FORBIDDEN);
    }
}