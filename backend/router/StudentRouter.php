<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

use DAO\StudentDAO;
use Service\StudentService;

return function (App $app) {
    $pdo = getPDO();

    $app->group('/student', function ($group) use ($pdo) {

        $group->get('/grades', function (Request $request, Response $response) use ($pdo) {
            $userId = $_SESSION['user_id'] ?? null;
            error_log("DEBUG SESSION: " . print_r($_SESSION, true));
            error_log('Session ID(studentgrades): ' . session_id());
            error_log('Session user_id: ' . ($_SESSION['user_id'] ?? 'null'));
            if (!$userId) {
                $response->getBody()->write(json_encode(['message' => 'Unauthorized']));
                return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
            }

            $dao = new StudentDAO($pdo, (int)$userId);
            $service = new StudentService($dao);

            try {
                $grades = $service->getGrades();
                $response->getBody()->write(json_encode($grades));
                return $response->withHeader('Content-Type', 'application/json');
            } catch (Exception $e) {
                $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
                return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
            }
        });


        $group->get('/ranking', function (Request $request, Response $response) use ($pdo) {
            $userId = $_SESSION['user_id'] ?? null;
            error_log('Session ID(studentranking): ' . session_id());
            error_log('Session user_id: ' . ($_SESSION['user_id'] ?? 'null'));
            if (!$userId) {
                $response->getBody()->write(json_encode(['message' => 'Unauthorized']));
                return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
            }

            $dao = new StudentDAO($pdo, (int)$userId);
            $service = new StudentService($dao);

            try {
                $ranking = $service->getRanking();
                $response->getBody()->write(json_encode($ranking));
                return $response->withHeader('Content-Type', 'application/json');
            } catch (Exception $e) {
                error_log("Ranking error: " . $e->getMessage());
                $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
                return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
            }
        });

        $group->get('/peers', function (Request $request, Response $response) use ($pdo) {
            $userId = $_SESSION['user_id'] ?? null;
            error_log('Session ID(studentcompare): ' . session_id());
            error_log('Session user_id: ' . ($_SESSION['user_id'] ?? 'null'));
            if (!$userId) {
                $response->getBody()->write(json_encode(['message' => 'Unauthorized']));
                return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
            }

            $dao = new StudentDAO($pdo, (int)$userId);
            $service = new StudentService($dao);

            try {
                $peers = $service->getPeers();
                $response->getBody()->write(json_encode($peers));
                return $response->withHeader('Content-Type', 'application/json');
            } catch (Exception $e) {
                error_log("Compare error: " . $e->getMessage());
                $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
                return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
            }
        });

    $group->get('/courses', function (Request $request, Response $response) use ($pdo) {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
        $response->getBody()->write(json_encode(['message' => 'Unauthorized']));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        $dao = new StudentDAO($pdo, (int)$userId);
        $service = new StudentService($dao);

        try {
        $courses = $service->getCourses();
        $response->getBody()->write(json_encode($courses));
        return $response->withHeader('Content-Type', 'application/json');
        } catch (Exception $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }

    
});

$group->get('/grades/{courseId}', function (Request $request, Response $response, $args) use ($pdo) {
    $userId = $_SESSION['user_id'] ?? null;
    $courseId = (int)$args['courseId'];

    error_log("GET /student/grades/$courseId for user_id=$userId");

    if (!$userId) {
        $response->getBody()->write(json_encode(['message' => 'Unauthorized']));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }

    $dao = new StudentDAO($pdo, (int)$userId);
    $service = new StudentService($dao);

    try {
        $grades = $service->getGradesByCourse($courseId);
        $response->getBody()->write(json_encode($grades));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (Exception $e) {
        error_log("getGradesByCourse error: " . $e->getMessage());
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});



    });
};
