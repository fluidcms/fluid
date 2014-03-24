<?php
namespace Fluid\Controller;

use Fluid\Controller;
use Fluid\Session\SessionEntity;

class LoginController extends Controller
{
    /**
     * @return string
     */
    public function index()
    {
        $this->response->body($this->loadView('login'));
    }
}