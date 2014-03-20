<?php
namespace Fluid;

/**
 * @param Request $request
 * @param CookieInterface $cookie
 * @return array
 */
return function (Router $router, Request $request, Response $response, CookieInterface $cookie) {
    $router
        ->respond('/', function () use ($request, $response, $cookie) {
            return (new Controllers\AdminController($request, $response, $cookie))->index();
        })
        ->respond('POST', '/session', function () use ($request, $response, $cookie) {
            return (new Controllers\SessionController($request, $response, $cookie))->create();
        });
};