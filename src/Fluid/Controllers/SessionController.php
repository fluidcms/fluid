<?php
namespace Fluid\Controllers;

use Fluid\Controller;
use Fluid\Response;
use Fluid\Session\SessionEntity;
use Fluid\User\UserCollection;

class SessionController extends Controller
{
    public function create()
    {
        $params = $this->request->params(['email', 'passwords']);
        if ($user = (new UserCollection($this->getStorage()))->findBy($params)) {
            die('found ya');
        }
        $this->response->code(Response::RESPONSE_CODE_BAD_REQUEST)->json(false);
    }
}