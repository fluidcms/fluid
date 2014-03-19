<?php
namespace Fluid\Controllers;

use Fluid\Controller;
use Fluid\Session\SessionEntity;

class AdminController extends Controller
{
    /**
     * @return string
     */
    public function index()
    {
        $session = $this->cookie->get(SessionEntity::COOKIE_NAME);
        if ($session === null) {
            return (new LoginController($this->getRequest(), $this->getCookie()))->index();
        }
        return null;
    }
}