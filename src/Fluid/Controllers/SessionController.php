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
        $params = $this->request->params(['email', 'password']);
        if ($user = (new UserCollection($this->getStorage()))->findOneBy(['email' => $params['email']])) {
            if ($user->testPassword($params['password'])) {
                $this->response->json(true);
                return;
            }
        }
        $this->response->code(Response::RESPONSE_CODE_BAD_REQUEST)->json(false);
    }
}