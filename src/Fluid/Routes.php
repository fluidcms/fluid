<?php
namespace Fluid;

/**
 * @param Request $request
 * @param CookieInterface $cookie
 * @return array
 */
return function (Request $request, CookieInterface $cookie) {
    return [
        '/admin/' =>
            function () use ($request, $cookie) {
                return (new Controllers\AdminController($request, $cookie))->index();
            },
    ];
};