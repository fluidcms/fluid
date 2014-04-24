<?php
namespace Fluid\Routes;

use Fluid\Fluid;
use Fluid\ConfigInterface;
use Fluid\Router;
use Fluid\Request;
use Fluid\Response;
use Fluid\CookieInterface;
use Fluid\StorageInterface;
use Fluid\Controller;
use Fluid\XmlMappingLoaderInterface;

/**
 * @param Fluid $fluid
 * @param ConfigInterface $config
 * @param Request $request
 * @param CookieInterface $cookie
 * @param StorageInterface $storage
 * @param XmlMappingLoaderInterface $xmlMappingLoader
 * @return array
 */
return function (Fluid $fluid, ConfigInterface $config, Router $router, Request $request, Response $response, StorageInterface $storage, XmlMappingLoaderInterface $xmlMappingLoader, CookieInterface $cookie) {
    $router
        ->respond('/', function () use ($fluid, $config, $router, $request, $response, $storage, $xmlMappingLoader, $cookie) {
            (new Controller\AdminController($fluid, $config, $router, $request, $response, $storage, $xmlMappingLoader, $cookie))->index();
        })
        ->respond('POST', '/session', function () use ($fluid, $config, $router, $request, $response, $storage, $xmlMappingLoader, $cookie) {
            (new Controller\SessionController($fluid, $config, $router, $request, $response, $storage, $xmlMappingLoader, $cookie))->create();
        })
        ->respond('POST', '/user', function () use ($fluid, $config, $router, $request, $response, $storage, $xmlMappingLoader, $cookie) {
            (new Controller\UserController($fluid, $config, $router, $request, $response, $storage, $xmlMappingLoader, $cookie))->create();
        })
        ->respond('GET', '/server', function () use ($fluid, $config, $router, $request, $response, $storage, $xmlMappingLoader, $cookie) {
            (new Controller\ServerController($fluid, $config, $router, $request, $response, $storage, $xmlMappingLoader, $cookie))->status();
        })
        ->respond('GET', '/components-icons/(.*)', function ($icon) use ($fluid, $config, $router, $request, $response, $storage, $xmlMappingLoader, $cookie) {
            (new Controller\ComponentController($fluid, $config, $router, $request, $response, $storage, $xmlMappingLoader, $cookie))->icon($icon);
        })
        ->respond('POST', '/file-upload', function () use ($fluid, $config, $router, $request, $response, $storage, $xmlMappingLoader, $cookie) {
            (new Controller\FileController($fluid, $config, $router, $request, $response, $storage, $xmlMappingLoader, $cookie))->upload();
        });
};