<?php
namespace Fluid;

/**
 * @param Request $request
 * @param CookieInterface $cookie
 * @param StorageInterface $storage
 * @return array
 */
return function (Router $router, Request $request, Response $response, StorageInterface $storage, CookieInterface $cookie) {
    $router
        ->respond('/', function () use ($request, $response, $storage, $cookie) {
            (new Controllers\AdminController($request, $response, $storage, $cookie))->index();
        })
        ->respond('POST', '/session', function () use ($request, $response, $storage, $cookie) {
            (new Controllers\SessionController($request, $response, $storage, $cookie))->create();
        })
        ->respond('POST', '/user', function () use ($request, $response, $storage, $cookie) {
            (new Controllers\UserController($request, $response, $storage, $cookie))->create();
        });
};