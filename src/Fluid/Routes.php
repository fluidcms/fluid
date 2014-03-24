<?php
namespace Fluid;

/**
 * @param Fluid $fluid
 * @param Request $request
 * @param CookieInterface $cookie
 * @param StorageInterface $storage
 * @return array
 */
return function (Fluid $fluid, Router $router, Request $request, Response $response, StorageInterface $storage, CookieInterface $cookie) {
    $router
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
        });
};