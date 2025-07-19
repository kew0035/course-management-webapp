<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

use DAO\AdvisorDAO;
use Service\AdvisorService;

return function (App $app) {
    $pdo = getPDO();

    $app->group('/advisor', function ($group) use ($pdo) {
        $group->get('/profile', function (Request $req, Response $res) use ($pdo) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $userId = $_SESSION['user_id'] ?? null;

            if (!$userId) {
                $res->getBody()->write(json_encode(['message' => 'Unauthorized']));
                return $res->withStatus(401)->withHeader('Content-Type', 'application/json');
            }


            $svc = new AdvisorService(new AdvisorDAO($pdo));
            $profile = $svc->getAdvisorProfileByUser($userId);

            if (!$profile) {
                $res->getBody()->write(json_encode(['message' => 'Advisor not found']));
                return $res->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            $res->getBody()->write(json_encode($profile));
            return $res->withHeader('Content-Type', 'application/json');
        });

        $group->get('/advisees', function (Request $request, Response $response) use ($pdo) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $userId = $_SESSION['user_id'] ?? null;

            if (!$userId) {
                $response->getBody()->write(json_encode(['message' => 'Unauthorized']));
                return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
            }

            $advisorService = new AdvisorService(new AdvisorDAO($pdo));

            $advId = $advisorService->getAdvisorIdByUser($userId);
            if (!$advId) {
                $response->getBody()->write(json_encode(['message' => 'Advisor not found']));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            $svc = new AdvisorService(new AdvisorDAO($pdo, $advId));

            try {
                $students = $svc->getAdvisees();
                $response->getBody()->write(json_encode($students));
                return $response->withHeader('Content-Type', 'application/json');
            } catch (Exception $e) {
                $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
                return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
            }
        });

        $group->get('/student/{id}', function (Request $req, Response $res, $args) use ($pdo) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) {
                $res->getBody()->write(json_encode(['message' => 'Unauthorized']));
                return $res->withStatus(401)->withHeader('Content-Type', 'application/json');
            }

            $advisorService = new AdvisorService(new AdvisorDAO($pdo));
            $advId = $advisorService->getAdvisorIdByUser($userId);
            if (!$advId) {
                $res->getBody()->write(json_encode(['message' => 'Advisor not found']));
                return $res->withStatus(403)->withHeader('Content-Type', 'application/json');
            }

            $svc = new AdvisorService(new AdvisorDAO($pdo, $advId));

            try {
                $studId = (int)$args['id'];
                $data = $svc->getStudentDetail($studId);

                if (!$data) {
                    $res->getBody()->write(json_encode(['message' => 'Student not found or not under your advisory']));
                    return $res->withStatus(404)->withHeader('Content-Type', 'application/json');
                }

                $res->getBody()->write(json_encode($data));
                return $res->withHeader('Content-Type', 'application/json');
            } catch (Exception $e) {
                $res->getBody()->write(json_encode(['error' => $e->getMessage()]));
                return $res->withStatus(500)->withHeader('Content-Type', 'application/json');
            }
        });

        $group->get('/student/{id}/notes', function (Request $req, Response $res, array $args) use ($pdo) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) {
                $res->getBody()->write(json_encode(['message' => 'Unauthorized']));
                return $res->withStatus(401)->withHeader('Content-Type', 'application/json');
            }

            $advisorService = new AdvisorService(new AdvisorDAO($pdo));
            $advId = $advisorService->getAdvisorIdByUser($userId);
            if (!$advId) {
                $res->getBody()->write(json_encode(['message' => 'Advisor not found']));
                return $res->withStatus(403)->withHeader('Content-Type', 'application/json');
            }

            $studId = (int) $args['id'];
            $svc = new AdvisorService(new AdvisorDAO($pdo, $advId));

            try {
                $notes = $svc->getNotesByStudent($studId);
                $res->getBody()->write(json_encode($notes));
                return $res->withHeader('Content-Type', 'application/json');
            } catch (Exception $e) {
                $res->getBody()->write(json_encode(['error' => $e->getMessage()]));
                return $res->withStatus(500)->withHeader('Content-Type', 'application/json');
            }
        });

        $group->post('/student/{id}/note', function (Request $req, Response $res, array $args) use ($pdo) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) {
                $res->getBody()->write(json_encode(['message' => 'Unauthorized']));
                return $res->withStatus(401)->withHeader('Content-Type', 'application/json');
            }

            $studId = (int) $args['id'];
            $data = $req->getParsedBody();
            $note = trim($data['note'] ?? '');

            $advisorService = new AdvisorService(new AdvisorDAO($pdo));
            $advId = $advisorService->getAdvisorIdByUser($userId);
            if (!$advId) {
                $res->getBody()->write(json_encode(['message' => 'Advisor not found']));
                return $res->withStatus(403)->withHeader('Content-Type', 'application/json');
            }

            $svc = new AdvisorService(new AdvisorDAO($pdo, $advId));
            try {
                $svc->saveNote($studId, $note);
                $res->getBody()->write(json_encode(['message' => 'Note saved']));
                return $res->withHeader('Content-Type', 'application/json');
            } catch (Exception $e) {
                $res->getBody()->write(json_encode(['error' => $e->getMessage()]));
                return $res->withStatus(500)->withHeader('Content-Type', 'application/json');
            }
        });

        $group->get('/export', function (Request $req, Response $res) use ($pdo) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) {
                $res->getBody()->write(json_encode(['message' => 'Unauthorized']));
                return $res->withStatus(401)->withHeader('Content-Type', 'application/json');
            }

            // Get advisorId from userId
            $advisorService = new AdvisorService(new AdvisorDAO($pdo));
            $advisorId = $advisorService->getAdvisorIdByUser($userId);
            if (!$advisorId) {
                $res->getBody()->write(json_encode(['message' => 'Advisor not found']));
                return $res->withStatus(403)->withHeader('Content-Type', 'application/json');
            }

            $svc = new AdvisorService(new AdvisorDAO($pdo, $advisorId));
            $rows = $svc->getConsultationReport();

            // Create CSV content
            $temp = fopen('php://temp', 'w+');
            fputcsv($temp, ['Matric No', 'Name', 'GPA', 'Note']);

            foreach ($rows as $row) {
                fputcsv($temp, [
                    $row['matric_no'] ?? '',
                    $row['stud_name'] ?? '',
                    $row['gpa'] ?? '',
                    $row['note'] ?? ''
                ]);
            }

            rewind($temp);
            $csv = stream_get_contents($temp);
            fclose($temp);

            $res->getBody()->write($csv);

            return $res
                ->withHeader('Content-Type', 'text/csv')
                ->withHeader('Content-Disposition', 'attachment; filename="advisor_report.csv"');
        });
    });
};
