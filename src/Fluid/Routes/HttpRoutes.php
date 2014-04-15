<?php
namespace Fluid\Routes;

use Fluid\Fluid;
use Fluid\Router;
use Fluid\Request;
use Fluid\Response;
use Fluid\CookieInterface;
use Fluid\StorageInterface;
use Fluid\Controller;
use Fluid\XmlMappingLoaderInterface;

/**
 * @param Fluid $fluid
 * @param Request $request
 * @param CookieInterface $cookie
 * @param StorageInterface $storage
 * @param XmlMappingLoaderInterface $xmlMappingLoader
 * @return array
 */
return function (Fluid $fluid, Router $router, Request $request, Response $response, StorageInterface $storage, XmlMappingLoaderInterface $xmlMappingLoader, CookieInterface $cookie) {
    $router
        ->respond('/', function () use ($fluid, $router, $request, $response, $storage, $xmlMappingLoader, $cookie) {
            (new Controller\AdminController($fluid, $router, $request, $response, $storage, $xmlMappingLoader, $cookie))->index();
        })
        ->respond('POST', '/session', function () use ($fluid, $router, $request, $response, $storage, $xmlMappingLoader, $cookie) {
            (new Controller\SessionController($fluid, $router, $request, $response, $storage, $xmlMappingLoader, $cookie))->create();
        })
        ->respond('POST', '/user', function () use ($fluid, $router, $request, $response, $storage, $xmlMappingLoader, $cookie) {
            (new Controller\UserController($fluid, $router, $request, $response, $storage, $xmlMappingLoader, $cookie))->create();
        })
        ->respond('GET', '/server', function () use ($fluid, $router, $request, $response, $storage, $xmlMappingLoader, $cookie) {
            (new Controller\ServerController($fluid, $router, $request, $response, $storage, $xmlMappingLoader, $cookie))->status();
        });
};