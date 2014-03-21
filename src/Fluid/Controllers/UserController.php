<?php
namespace Fluid\Controllers;

use Fluid\Controller;
use Fluid\Response;
use Fluid\Session\SessionEntity;
use Fluid\User\UserCollection;
use Fluid\User\UserEntity;

class UserController extends Controller
{
    public function create()
    {
        $params = $this->request->params(['name', 'email', 'passwords']);
        $userCollection = new UserCollection($this->getStorage());
        if ($userCollection->findBy(['email' => $params['email']])) {
            $this->response->code(Response::RESPONSE_CODE_BAD_REQUEST)->json(['errors' => 'email_exists']);
            return null;
        }
        $user = new UserEntity;

        if ($validation = $user->validate($params) === true) {
            $user->setEmail($params['email'])
                ->setPassword($params['password'])
                ->setName($params['name']);

            $userCollection->save($user);
        }

        $this->response->code(Response::RESPONSE_CODE_BAD_REQUEST)->json(['errors' => $validation]);
        return null;
    }
}