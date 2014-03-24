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
        $params = $this->request->params(['name', 'email', 'password']);
        $users = new UserCollection($this->getStorage());
        if ($users->findOneBy(['email' => $params['email']])) {
            $this->response->code(Response::RESPONSE_CODE_BAD_REQUEST)->json(['errors' => ['email_exists' => 'email_exists']]);
            return null;

        }
        $user = new UserEntity($this->getStorage());
        $validation = $user->validate($params);

        if ($validation === true) {
            $user->setEmail($params['email'])
                ->createPasswordHash($params['password'])
                ->setName($params['name']);

            $users->create($user);
            $users->save($user);
            $this->response->json($user->toArray());
            return;
        }

        $this->response->code(Response::RESPONSE_CODE_BAD_REQUEST)->json(['errors' => $validation]);
    }
}