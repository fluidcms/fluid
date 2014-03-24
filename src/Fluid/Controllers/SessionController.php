<?php
namespace Fluid\Controllers;

use Fluid\Controller;
use Fluid\Response;
use Fluid\Session\SessionCollection;
use Fluid\Session\SessionEntity;
use Fluid\User\UserCollection;

class SessionController extends Controller
{
    public function create()
    {
        $params = $this->request->params(['email', 'password']);
        $userCollection = new UserCollection($this->getStorage());
        if ($user = (new UserCollection($this->getStorage()))->findOneBy(['email' => $params['email']])) {
            if ($user->testPassword($params['password'])) {
                $session = (new SessionEntity($userCollection))->setUser($user);
                $sessions = new SessionCollection($this->getStorage(), $userCollection);
                $sessions->create($session);
                $sessions->save($session);
                $this->response->json($session->getToken());
                return;
            }
        }
        $this->response->code(Response::RESPONSE_CODE_BAD_REQUEST)->json(false);
    }
}