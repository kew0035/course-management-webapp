<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use App\backend\LoginPage;

require_once __DIR__ . '/../LoginPage.php';
require_once __DIR__ . '/../config/database.php';

return function (App $app) {
    $pdo = getPDO();

    $app->addBodyParsingMiddleware();

    $app->post('/login', function (Request $request, Response $response) use ($pdo) {
        $data = $request->getParsedBody();
        $result = LoginPage::handleLogin($pdo, $data);


        if ($result['status'] === 200) {
            $_SESSION['user_id'] = $result['data']['user_id'];  // sore user_id in session
            error_log('Session ID(login): ' . session_id());
            error_log('Session user_id: ' . ($_SESSION['user_id'] ?? 'null'));
        }

        $response->getBody()->write(json_encode($result['data']));
        return $response
            ->withStatus($result['status'])
            ->withHeader('Content-Type', 'application/json');
    });

    $app->post('/logout', function (Request $request, Response $response) {
        session_start();
        session_unset();
        session_destroy();
        setcookie(session_name(), '', time() - 3600, '/');

        $response->getBody()->write(json_encode(['message' => 'Logged out successfully']));
        return $response->withHeader('Content-Type', 'application/json');
    });
};
