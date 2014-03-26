<?php
namespace Fluid\Routes;

use Fluid\Fluid;
use Fluid\Router;
use Fluid\Request;
use Fluid\Response;
use Fluid\CookieInterface;
use Fluid\Session\SessionCollection;
use Fluid\Session\SessionEntity;
use Fluid\StorageInterface;
use Fluid\Controller;
use Fluid\User\UserCollection;
use Fluid\User\UserEntity;

/**
 * @param Fluid $fluid
 * @param Request $request
 * @param CookieInterface $cookie
 * @param StorageInterface $storage
 * @return array
 */
return function (Router $router, Request $request, Response $response, StorageInterface $storage, UserCollection $users, UserEntity $user, SessionCollection $sessions, SessionEntity $session) {
    /*$router
        ->respond('/', function () use ($fluid, $router, $request, $response, $storage, $cookie) {
            (new Controller\AdminController($fluid, $router, $request, $response, $storage, $cookie))->index();
        })
        ->respond('POST', '/session', function () use ($fluid, $router, $request, $response, $storage, $cookie) {
            (new Controller\SessionController($fluid, $router, $request, $response, $storage, $cookie))->create();
        })
        ->respond('POST', '/user', function () use ($fluid, $router, $request, $response, $storage, $cookie) {
            (new Controller\UserController($fluid, $router, $request, $response, $storage, $cookie))->create();
        })
        ->respond('GET', '/server', function () use ($fluid, $router, $request, $response, $storage, $cookie) {
            (new Controller\ServerController($fluid, $router, $request, $response, $storage, $cookie))->status();
        });*/
};