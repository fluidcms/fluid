<?php
namespace Fluid\Routes;

use Fluid\Fluid;
use Fluid\ConfigInterface;
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
use Fluid\XmlMappingLoaderInterface;

/**
 * @param Fluid $fluid
 * @param ConfigInterface $config
 * @param Request $request
 * @param CookieInterface $cookie
 * @param StorageInterface $storage
 * @return array
 */
return function (Fluid $fluid, ConfigInterface $config, Router $router, Request $request, Response $response, StorageInterface $storage, XmlMappingLoaderInterface $xmlMappingLoader, UserCollection $users, UserEntity $user, SessionCollection $sessions, SessionEntity $session) {
    // Pages routes
    $router->respond('/pages', function () use ($fluid, $config, $router, $request, $response, $storage, $xmlMappingLoader, $users, $user, $sessions, $session) {
        $controller = new Controller\PageController($fluid, $config, $router, $request, $response, $storage, $xmlMappingLoader, null);
        $controller->setSessionDependencies($users, $user, $sessions, $session);
        if ($request->getMethod() === 'GET') {
            $controller->getAll();
        } else {
            $response->setCode(Response::RESPONSE_CODE_METHOD_NOT_ALLOWED);
        }
    });

    // Page routes
    $router->respond('/page/(.+)', function ($page) use ($fluid, $config, $router, $request, $response, $storage, $xmlMappingLoader, $users, $user, $sessions, $session) {
        $controller = new Controller\PageController($fluid, $config, $router, $request, $response, $storage, $xmlMappingLoader, null);
        $controller->setSessionDependencies($users, $user, $sessions, $session);
        if ($request->getMethod() === 'GET') {
            $controller->get($page);
        } elseif ($request->getMethod() === 'POST') {
            $controller->post($page);
        } else {
            $response->setCode(Response::RESPONSE_CODE_METHOD_NOT_ALLOWED);
        }
    });

    // Components routes
    $router->respond('/component', function ($component = null) use ($fluid, $config, $router, $request, $response, $storage, $xmlMappingLoader, $users, $user, $sessions, $session) {
        $controller = new Controller\ComponentController($fluid, $config, $router, $request, $response, $storage, $xmlMappingLoader, null);
        $controller->setSessionDependencies($users, $user, $sessions, $session);
        if (null === $component && $request->getMethod() === 'GET') {
            $controller->getAll();
        } else {
            $response->setCode(Response::RESPONSE_CODE_METHOD_NOT_ALLOWED);
        }
    });
};