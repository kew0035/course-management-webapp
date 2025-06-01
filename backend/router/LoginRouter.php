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

        $response->getBody()->write(json_encode($result['data']));
        return $response
            ->withStatus($result['status'])
            ->withHeader('Content-Type', 'application/json');
    });
};