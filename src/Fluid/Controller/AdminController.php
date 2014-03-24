<?php
namespace Fluid\Controller;

use Fluid\Controller;
use Fluid\Session\SessionCollection;
use Fluid\Session\SessionEntity;
use Fluid\User\UserCollection;
use Fluid\User\UserEntity;
use Fluid\Helper\SessionHelper;

class AdminController extends Controller
{
    use SessionHelper;

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
                $this->response->body($this->loadView('app', [
                    'session' => $session,
                    'branch' => $this->fluid->getConfig()->getBranch(),
                    'path' => $this->router->getAdminPath(),
                    'websocket' => 'ws://' . $this->request->getUrl() . ':' . $this->fluid->getConfig()->getWebsocketPort() . $this->router->getAdminPath(),
                    'user' => $user,
                    'language' => $this->fluid->getConfig()->getLanguage(),
                    'languages' => $this->fluid->getConfig()->getLanguages()
                ]));
                return;
            }
        }
        $this->cookie->delete(SessionEntity::COOKIE_NAME);
        (new LoginController($this->getFluid(), $this->getRouter(), $this->getRequest(), $this->getResponse(), $this->getStorage(), $this->getCookie()))->index();
    }
}