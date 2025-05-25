<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use DAO\StudentDAO;
use Service\StudentService;
use Slim\Middleware\BodyParsingMiddleware;

require __DIR__ . '/../config/database.php';

return function (App $app) {
    $pdo = getPDO();
    
    $app->addBodyParsingMiddleware();

    $studentDAO = new StudentDAO($pdo);
    $studentService = new StudentService($studentDAO);

    $app->get('/students', function (Request $request, Response $response) use ($studentService) {
        $students = $studentService->listStudents();
        $response->getBody()->write(json_encode($students));
        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->post('/students', function (Request $request, Response $response) use ($studentService) {
        $data = $request->getParsedBody();
        $studentService->addStudent($data);
        $response->getBody()->write(json_encode(['message' => 'Student added']));
        return $response->withHeader('Content-Type', 'application/json');
    });

};