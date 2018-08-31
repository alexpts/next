<?php
declare(strict_types=1);

use Psr\Http\Message\ServerRequestInterface;
use PTS\NextRouter\Router;
use PTS\PSR15\Middlewares\ResponseEmit;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequestFactory;

require_once '../vendor/autoload.php';

$app = new Router;
$responseEmitter = require 'include/ResponseEmitter.php';

$handler = [
    'fetch user' => function (ServerRequestInterface $request, $next) {
        $user = 'some user object';
        // ...  find $user by id
        if (null === $user) {
            throw new \Exception('User not found', 404);
        }

        $request = $request->withAttribute('user', $user);
    },
    'controller' => function (ServerRequestInterface $request, $next) {
        $user = $request->getAttribute('user');
        return new JsonResponse(['user' => $user], 200);
    },
];

$app->getStore()
    ->middleware(new ResponseEmit($responseEmitter))
    ->pipe($handler, ['path' => '/users', 'method' => ['GET']])
    ->use(function (ServerRequestInterface $request, $next) {
        return new JsonResponse(['message' => 'otherwise']);
    });

$request = ServerRequestFactory::fromGlobals();
$response = $app->handle($request);

