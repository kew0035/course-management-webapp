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


        $group->get('/ranking/{courseId}', function (Request $request, Response $response, array $args) use ($pdo) {
            $userId = $_SESSION['user_id'] ?? null;
            error_log('Session ID(studentranking): ' . session_id());
            error_log('Session user_id: ' . ($_SESSION['user_id'] ?? 'null'));
            
            if (!$userId) {
                $response->getBody()->write(json_encode(['message' => 'Unauthorized']));
                return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
            }
        
            $courseId = (int)$args['courseId']; // 获取课程ID
        
            $dao = new StudentDAO($pdo, (int)$userId);
            $service = new StudentService($dao);
        
            try {
                $ranking = $service->getRanking($courseId); // 传递课程ID给 getRanking 方法
                $response->getBody()->write(json_encode($ranking));
                return $response->withHeader('Content-Type', 'application/json');
            } catch (Exception $e) {
                error_log("Ranking error: " . $e->getMessage());
                $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
                return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
            }
        });
        
        // $group->get('/ranking', function (Request $request, Response $response) use ($pdo) {
        //     $userId = $_SESSION['user_id'] ?? null;
        //     error_log('Session ID(studentranking): ' . session_id());
        //     error_log('Session user_id: ' . ($_SESSION['user_id'] ?? 'null'));
        //     if (!$userId) {
        //         $response->getBody()->write(json_encode(['message' => 'Unauthorized']));
        //         return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        //     }

        //     $dao = new StudentDAO($pdo, (int)$userId);
        //     $service = new StudentService($dao);

        //     try {
        //         $ranking = $service->getRanking();
        //         $response->getBody()->write(json_encode($ranking));
        //         return $response->withHeader('Content-Type', 'application/json');
        //     } catch (Exception $e) {
        //         error_log("Ranking error: " . $e->getMessage());
        //         $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        //         return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        //     }
        // });

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

    $group->get('/advisor', function (Request $request, Response $response) use ($pdo) {
            $userId = $_SESSION['user_id'] ?? null;

            if (!$userId) {
                $response->getBody()->write(json_encode(['message' => 'Unauthorized']));
                return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
            }

            $dao = new \DAO\StudentDAO($pdo, (int)$userId);
            $service = new \Service\StudentService($dao);

            try {
                $notes = $service->getAdvisorNotes();
                $response->getBody()->write(json_encode($notes));
                return $response->withHeader('Content-Type', 'application/json');
            } catch (\Exception $e) {
                $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
                return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
            }
        });


        $group->get('/appeal/{scm_id}', function (Request $request, Response $response, array $args) use ($pdo) {
            $userId = $_SESSION['user_id'] ?? null;
    
            if (!$userId) {
                $response->getBody()->write(json_encode(['message' => 'Unauthorized']));
                return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
            }
    
            $dao = new StudentDAO($pdo, (int)$userId);
            $service = new StudentService($dao);
            $scm_id = (int)$args['scm_id'];
            $appeal = $service->getAppealByScmId($scm_id,$userId);
    
            if ($appeal) {
                $response->getBody()->write(json_encode($appeal));
            } else {
                $response->getBody()->write(json_encode([])); // No appeal found
            }
    
            return $response->withHeader('Content-Type', 'application/json');
        });
    
    
        $group->post('/appeal', function (Request $request, Response $response) use ($pdo) {
        $userId = $_SESSION['user_id'] ?? null;
    
        if (!$userId) {
            $response->getBody()->write(json_encode(['message' => 'Unauthorized']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }
    
        $data = $request->getParsedBody();
        $scm_id = $data['scm_id'] ?? null;
        $reason = $data['reason'] ?? null;
    
        $dao = new StudentDAO($pdo, (int)$userId);
        $service = new StudentService($dao);
    
        $result = $service->submitAppeal($scm_id, $reason, $userId);
    
        $response->getBody()->write(json_encode(['message' => $result['message']]));
        return $response->withStatus($result['status'])->withHeader('Content-Type', 'application/json');
    });


    // $group->post('/appeal', function (Request $request, Response $response) use ($pdo) {
    //         $userId = $_SESSION['user_id'] ?? null;

    //         if (!$userId) {
    //             $response->getBody()->write(json_encode(['message' => 'Unauthorized']));
    //             return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    //         }

    //         $data = $request->getParsedBody();
    //         $scm_id = $data['scm_id'] ?? null;
    //         $reason = $data['reason'] ?? null;

    //         if (!$scm_id || !$reason) {
    //             $response->getBody()->write(json_encode(['message' => 'Missing required fields: scm_id and reason.']));
    //             return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    //         }

    //         // Verify scm_id belongs to logged-in student
    //         $stmt = $pdo->prepare("
    //             SELECT scm.scm_id
    //             FROM student_continuous_marks scm
    //             JOIN student_grades sg ON scm.sg_id = sg.sg_id
    //             JOIN students s ON sg.stud_id = s.stud_id
    //             WHERE scm.scm_id = ? AND s.user_id = ?
    //         ");
    //         $stmt->execute([$scm_id, $userId]);
    //         if (!$stmt->fetch()) {
    //             $response->getBody()->write(json_encode(['message' => 'Invalid scm_id or unauthorized access.']));
    //             return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
    //         }

    //         // Prevent duplicate appeals for the same scm_id
    //         $checkStmt = $pdo->prepare("SELECT 1 FROM grade_appeals WHERE scm_id = ?");
    //         $checkStmt->execute([$scm_id]);
    //         if ($checkStmt->fetch()) {
    //             $response->getBody()->write(json_encode(['message' => 'Appeal already submitted for this component.']));
    //             return $response->withStatus(409)->withHeader('Content-Type', 'application/json');
    //         }

    //         // Insert the appeal
    //         $insertStmt = $pdo->prepare("
    //             INSERT INTO grade_appeals (scm_id, reason, status) 
    //             VALUES (?, ?, 'pending')
    //         ");
    //         $insertStmt->execute([$scm_id, $reason]);

    //         $response->getBody()->write(json_encode(['message' => 'Appeal submitted successfully.']));
    //         return $response->withHeader('Content-Type', 'application/json');
    //     });



    });
};
