<?php
namespace Fluid\Controller;

use Fluid\Controller;
use Fluid\Session\SessionEntity;
use Fluid\User\UserEntity;
use Fluid\Helper\SessionHelper;
use Fluid\WebsocketServer\LocalWebSocketServer;

class AdminController extends Controller
{
    use SessionHelper;

    /**
     * @return string
     */
    public function index()
    {
        $session = $this->getSession();
        $user = $this->getUser();
        if ($user instanceof UserEntity) {
            $this->response->body($this->loadView('app', [
                'session' => $session,
                'branch' => $this->fluid->getConfig()->getBranch(),
                'path' => $this->router->getAdminPath(),
                'websocket' => 'ws://' . $this->request->getUrl() . $this->router->getAdminPath() . LocalWebSocketServer::URI,
                'user' => $user,
                'language' => $this->fluid->getConfig()->getLanguage(),
                'languages' => $this->fluid->getConfig()->getLanguages()
            ]));
            return;
        }
        $this->cookie->delete(SessionEntity::COOKIE_NAME);
        (new LoginController($this->getFluid(), $this->getConfig(), $this->getRouter(), $this->getRequest(), $this->getResponse(), $this->getStorage(), $this->getXmlMappingLoader(), $this->getCookie()))->index();
    }
}