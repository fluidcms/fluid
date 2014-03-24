<?php
namespace Fluid\Controllers;

use Fluid\Controller;
use Fluid\Session\SessionCollection;
use Fluid\Session\SessionEntity;
use Fluid\User\UserCollection;
use Fluid\User\UserEntity;

class AdminController extends Controller
{
    /**
     * @return string
     */
    public function index()
    {
        $session = $this->cookie->get(SessionEntity::COOKIE_NAME);
        $userCollection = new UserCollection($this->getStorage());
        $sessionCollection = new SessionCollection($this->getStorage(), $userCollection);
        $session = $sessionCollection->find($session);
        if ($session instanceof SessionEntity && !$session->isExpired()) {
            $user = $session->getUser();
            if ($user instanceof UserEntity) {
                echo 'Logged in';
                return;
            }
        }
        $this->cookie->delete(SessionEntity::COOKIE_NAME);
        (new LoginController($this->getRequest(), $this->getResponse(), $this->getStorage(), $this->getCookie()))->index();
    }
}