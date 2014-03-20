<?php
namespace Fluid\Controllers;

use Fluid\Controller;
use Fluid\Session\SessionEntity;
use Fluid\User\UserCollection;

class SessionController extends Controller
{
    /**
     * @return string
     */
    public function create()
    {
        $params = $this->request->params(['email', 'passwords']);
        if ($user = (new UserCollection)->findBy($params)) {
            die('found ya');
        }
        return json_encode(false);
    }
}