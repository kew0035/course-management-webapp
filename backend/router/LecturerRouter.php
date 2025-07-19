<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

use Service\LecturerService;
use DAO\LecturerDAO;

return function (App $app) {
    $pdo = getPDO();

    $app->group('/lecturer', function ($group) use ($pdo) {
        //get course title
        $group->get('/course-name', function (Request $req, Response $res) use ($pdo) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $userId = $_SESSION['user_id'] ?? null;
            error_log("Session ID: " . session_id());
            error_log("User ID: " . ($_SESSION['user_id'] ?? 'not set'));

            if (!$userId) {
                $res->getBody()->write(json_encode(['message' => 'Unauthorized']));
                return $res->withStatus(401)->withHeader('Content-Type', 'application/json');
            }

            $dao = new LecturerDAO($pdo, (int)$userId);
            $service = new LecturerService($dao);

            try {
                $courseDetails = $service->getCourseDetails();
                $res->getBody()->write(json_encode($courseDetails));
                return $res->withHeader('Content-Type', 'application/json');
            } catch (Exception $e) {
                $res->getBody()->write(json_encode(['error' => $e->getMessage()]));
                return $res->withStatus(500)->withHeader('Content-Type', 'application/json');
            }
        });

        //end get course title
        $group->get('/students', function (Request $req, Response $res) use ($pdo) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $userId = $_SESSION['user_id'] ?? null;
            error_log("Session ID: " . session_id());
            error_log("User ID: " . ($_SESSION['user_id'] ?? 'not set'));

            if (!$userId) {
                $res->getBody()->write(json_encode(['message' => 'Unauthorized']));
                return $res->withStatus(401)->withHeader('Content-Type', 'application/json');
            }

            $dao = new LecturerDAO($pdo, (int)$userId);
            $service = new LecturerService($dao);


            try {
                $students = $service->getStudents();
                $res->getBody()->write(json_encode($students));
                return $res->withHeader('Content-Type', 'application/json');
            } catch (Exception $e) {
                $res->getBody()->write(json_encode(['error' => $e->getMessage()]));
                return $res->withStatus(500)->withHeader('Content-Type', 'application/json');
            }
        });


        $group->post('/update-scores', function (Request $request, Response $response) use ($pdo) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $userId = $_SESSION['user_id'] ?? null;

            if (!$userId) {
                $response->getBody()->write(json_encode(['message' => 'Unauthorized']));
                return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
            }

            $data = $request->getParsedBody();
            $matric_no = $data['matric_no'] ?? '';
            $continuous_marks = $data['continuous_marks'] ?? [];
            $final_exam = $data['final_exam'] ?? 0;

            if (!$matric_no) {
                $response->getBody()->write(json_encode(['message' => 'Matric number required']));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }

            $dao = new LecturerDAO($pdo, (int)$userId);
            $service = new LecturerService($dao);

            $success = $service->updateScores($matric_no, $continuous_marks, $final_exam);
            if (!$success) {
                $response->getBody()->write(json_encode(['message' => 'Student not found']));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            $response->getBody()->write(json_encode(['message' => 'Scores updated']));
            return $response->withHeader('Content-Type', 'application/json');
        });


        $group->get('/components', function (Request $request, Response $response) use ($pdo) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $userId = $_SESSION['user_id'] ?? null;

            if (!$userId) {
                $response->getBody()->write(json_encode(['message' => 'Unauthorized']));
                return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
            }

            $dao = new LecturerDAO($pdo, (int)$userId);
            $service = new LecturerService($dao);

            $components = $service->getComponents();
            $response->getBody()->write(json_encode($components));
            return $response->withHeader('Content-Type', 'application/json');
        });


        $group->post('/component/save', function (Request $request, Response $response) use ($pdo) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $userId = $_SESSION['user_id'] ?? null;

            if (!$userId) {
                $response->getBody()->write(json_encode(['message' => 'Unauthorized']));
                return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
            }

            $data = json_decode($request->getBody()->getContents(), true);
            $component = trim($data['name'] ?? '');
            $originalName = trim($data['originalName'] ?? '') ?: null;
            $maxMark = (int)($data['maxMark'] ?? 0);
            $weight = (int)($data['weight'] ?? 0);

            if (trim($component) === '' || $maxMark <= 0 || $weight <= 0) {
                $response->getBody()->write(json_encode(['message' => 'Invalid input']));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }

            $dao = new LecturerDAO($pdo, $userId);
            $service = new LecturerService($dao);

            try {
                $service->saveComponent($component, $maxMark, $weight, $originalName);
                $response->getBody()->write(json_encode(['message' => 'Component saved']));
                return $response->withHeader('Content-Type', 'application/json');
            } catch (Exception $e) {
                $response->getBody()->write(json_encode(['message' => 'Save failed', 'error' => $e->getMessage()]));
                return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
            }
        });


        $group->post('/component/delete', function (Request $request, Response $response) use ($pdo) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $userId = $_SESSION['user_id'] ?? null;

            if (!$userId) {
                $response->getBody()->write(json_encode(['message' => 'Unauthorized']));
                return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
            }

            $data = $request->getParsedBody();
            $component = $data['name'] ?? '';

            if (!$component) {
                $response->getBody()->write(json_encode(['message' => 'Component name required']));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }

            $dao = new LecturerDAO($pdo, (int)$userId);
            $service = new LecturerService($dao);

            $service->deleteComponent($component);
            $response->getBody()->write(json_encode(['message' => 'Component deleted']));
            return $response->withHeader('Content-Type', 'application/json');
        });


        $group->post('/sync-student-marks', function (Request $request, Response $response) use ($pdo) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $userId = $_SESSION['user_id'] ?? null;

            if (!$userId) {
                $response->getBody()->write(json_encode(['message' => 'Unauthorized']));
                return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
            }

            $dao = new LecturerDAO($pdo, (int)$userId);
            $service = new LecturerService($dao);

            $service->syncStudentMarks();
            $response->getBody()->write(json_encode(['message' => 'Student marks synchronized']));
            return $response->withHeader('Content-Type', 'application/json');
        });


        $group->get('/appeals', function (Request $request, Response $response) use ($pdo) {
            $userId = $_SESSION['user_id'] ?? null;

            if (!$userId) {
                $response->getBody()->write(json_encode(['message' => 'Unauthorized']));
                return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
            }

            // Get all appeals tied to this lecturer's courses
            $stmt = $pdo->prepare("
                SELECT
                    ga.scm_id,
                    ga.status,
                    ga.reason,
                    scm.component,
                    s.stud_name AS student_name,
                    s.matric_no,
                    c.course_id,
                    c.course_name
                FROM grade_appeals ga
                JOIN student_continuous_marks scm ON ga.scm_id = scm.scm_id
                JOIN student_grades sg ON scm.sg_id = sg.sg_id
                JOIN students s ON sg.stud_id = s.stud_id
                JOIN courses c ON sg.course_id = c.course_id
                JOIN lecturers l ON c.lec_id = l.lec_id
                WHERE l.user_id = ?
                ORDER BY ga.status = 'pending' DESC, c.course_code, scm.component
            ");
            $stmt->execute([$userId]);
            $appeals = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response->getBody()->write(json_encode($appeals));
            return $response->withHeader('Content-Type', 'application/json');
        });


        $group->post('/appeal/respond', function (Request $request, Response $response) use ($pdo) {
            $userId = $_SESSION['user_id'] ?? null;

            if (!$userId) {
                $response->getBody()->write(json_encode(['message' => 'Unauthorized']));
                return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
            }

            $data = $request->getParsedBody();
            $scm_id = $data['scm_id'] ?? null;
            $status = $data['status'] ?? null;

            if (!$scm_id || !in_array($status, ['approved', 'rejected'])) {
                $response->getBody()->write(json_encode(['message' => 'Unauthorized']));
                return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
            }

            // Ensure appeal belongs to lecturer's course
            $stmt = $pdo->prepare("
                SELECT 1
                FROM grade_appeals ga
                JOIN student_continuous_marks scm ON ga.scm_id = scm.scm_id
                JOIN student_grades sg ON scm.sg_id = sg.sg_id
                JOIN courses c ON sg.course_id = c.course_id
                JOIN lecturers l ON c.lec_id = l.lec_id
                WHERE ga.scm_id = ? AND l.user_id = ?
            ");
            $stmt->execute([$scm_id, $userId]);
            $valid = $stmt->fetch();

            if (!$valid) {
                $response->getBody()->write(json_encode(['message' => 'Unauthorized']));
                return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
            }

            // Update appeal status
            $update = $pdo->prepare("UPDATE grade_appeals SET status = ? WHERE scm_id = ?");
            $update->execute([$status, $scm_id]);

            $response->getBody()->write(json_encode(['message' => 'Appeal status updated']));
            return $response->withHeader('Content-Type', 'application/json');
        });
    });
};
