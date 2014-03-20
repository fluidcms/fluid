<?php
namespace Fluid;

/**
 * @param Request $request
 * @param CookieInterface $cookie
 * @return array
 */
return function (Router $router, Request $request, CookieInterface $cookie) {
    $router->respond('/', function () use ($request, $cookie) {
        return (new Controllers\AdminController($request, $cookie))->index();
    });
};