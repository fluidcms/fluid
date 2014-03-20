<?php
namespace Fluid;

/**
 * @param Request $request
 * @param CookieInterface $cookie
 * @return array
 */
return function (Router $router, Request $request, CookieInterface $cookie) {
    $router
        ->respond('/', function () use ($request, $cookie) {
            return (new Controllers\AdminController($request, $cookie))->index();
        })
        ->respond('POST', '/session', function () use ($request, $cookie) {
            return (new Controllers\SessionController($request, $cookie))->create();
        });
};