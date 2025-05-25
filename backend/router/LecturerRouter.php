<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

require_once __DIR__ . '/../service/LecturerService.php';
require_once __DIR__ . '/../dao/LecturerDAO.php';
require_once __DIR__ . '/../config/database.php';

return function (App $app) {
    $pdo = getPDO();
    $dao = new LecturerDAO($pdo);
    $service = new LecturerService($dao);

    $app->group('/lecturer', function ($group) use ($service) {

        $group->get('/students', function (Request $request, Response $response) use ($service) {
            $students = $service->getStudents();
            $response->getBody()->write(json_encode($students));
            return $response->withHeader('Content-Type', 'application/json');
        });

        $group->post('/update-scores', function (Request $request, Response $response) use ($service) {
            $data = $request->getParsedBody();

            $matric_no = $data['matric_no'] ?? '';
            $continuous_marks = $data['continuous_marks'] ?? [];
            $final_exam = $data['final_exam'] ?? 0;

            if (!$matric_no) {
                $response->getBody()->write(json_encode(['message' => 'Matric number required']));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }

            $success = $service->updateScores($matric_no, $continuous_marks, $final_exam);
            if (!$success) {
                $response->getBody()->write(json_encode(['message' => 'Student not found']));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            $response->getBody()->write(json_encode(['message' => 'Scores updated']));
            return $response->withHeader('Content-Type', 'application/json');
        });

        $group->get('/components', function (Request $request, Response $response) use ($service) {
            $components = $service->getComponents();
            $response->getBody()->write(json_encode($components));
            return $response->withHeader('Content-Type', 'application/json');
        });

        $group->post('/component/save', function (Request $request, Response $response) use ($service) {
            $data = json_decode($request->getBody()->getContents(), true);

            $component = trim($data['name'] ?? '');
            $maxMark = (int)($data['maxMark'] ?? 0);
            $weight = (int)($data['weight'] ?? 0);

            if (trim($component) === '' || $maxMark <= 0 || $weight <= 0) {
                $response->getBody()->write(json_encode(['message' => 'Invalid input']));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }

            try {
                $service->saveComponent($component, $maxMark, $weight);
                $response->getBody()->write(json_encode(['message' => 'Component saved']));
                return $response->withHeader('Content-Type', 'application/json');
            } catch (Exception $e) {
                $response->getBody()->write(json_encode(['message' => 'Save failed', 'error' => $e->getMessage()]));
                return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
            }
        });

        $group->post('/component/delete', function (Request $request, Response $response) use ($service) {
            $data = $request->getParsedBody();
            $component = $data['name'] ?? '';

            if (!$component) {
                $response->getBody()->write(json_encode(['message' => 'Component name required']));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }

            $service->deleteComponent($component);
            $response->getBody()->write(json_encode(['message' => 'Component deleted']));
            return $response->withHeader('Content-Type', 'application/json');
        });

        $group->post('/sync-student-marks', function (Request $request, Response $response) use ($service) {
            $service->syncStudentMarks();
            $response->getBody()->write(json_encode(['message' => 'Student marks synchronized']));
            return $response->withHeader('Content-Type', 'application/json');
        });
    });
};
