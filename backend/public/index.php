<?php
session_start();

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Slim\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

$app = AppFactory::create();


$app->options('/{routes:.+}', function (Request $request, Response $response) {
    return $response;
});



$app->add(function (Request $request, RequestHandler $handler): Response {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $response = $handler->handle($request);

    $origin = $request->getHeaderLine('Origin') ?: '*';
    $method = strtoupper($request->getMethod());

    $response = $response
        ->withHeader('Access-Control-Allow-Origin', $origin)
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->withHeader('Access-Control-Allow-Credentials', 'true');

    if ($method === 'OPTIONS') {
        return $response->withStatus(200);
    }

    return $response;
});

$app->addBodyParsingMiddleware();


(require __DIR__ . '/../router/AuthRouter.php')($app);
(require __DIR__ . '/../router/StudentRouter.php')($app);
(require __DIR__ . '/../router/LecturerRouter.php')($app);
(require __DIR__ . '/../router/AdvisorRouter.php')($app);


$app->run();