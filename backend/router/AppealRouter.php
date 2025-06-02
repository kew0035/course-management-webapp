<?php
use Slim\App;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require_once __DIR__ . '/../config/database.php';

return function (App $app) {
    $pdo = getPDO();

    // Submit grade appeals
    $app->post('/appeal/submit', function (Request $request, Response $response) use ($pdo) {
        $data = $request->getParsedBody();
        $stud_id = $data['stud_id'] ?? null;
        $course_id = $data['course_id'] ?? null;
        $component = $data['component'] ?? null;
        $reason = $data['reason'] ?? null;
        error_log("Received: stud_id=$stud_id, course_id=$course_id, component=$component, reason=$reason");
        if (!$stud_id || !$course_id || !$component || !$reason) {
          
          $response->getBody()->write(json_encode(['message' => 'Missing required fields.']));
          return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
      }
      
        // Preventing duplicate submissions
        $stmt = $pdo->prepare("SELECT * FROM grade_appeals WHERE stud_id = ? AND course_id = ? AND component = ?");
        $stmt->execute([$stud_id, $course_id, $component]);
        if ($stmt->fetch()) {
          $response->getBody()->write(json_encode(['message' => 'Appeal already submitted.']));
          return $response->withStatus(409)->withHeader('Content-Type', 'application/json');
      }
      

        $stmt = $pdo->prepare("INSERT INTO grade_appeals (stud_id, course_id, component, reason, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->execute([$stud_id, $course_id, $component, $reason]);

        $response->getBody()->write(json_encode(['message' => 'Appeal submitted successfully.']));
        return $response->withHeader('Content-Type', 'application/json');
    });

    // Get the specified appeal status
    $app->get('/appeal/{stud_id}/{course_id}/{component}', function (Request $request, Response $response, array $args) use ($pdo) {
        $stud_id = $args['stud_id'];
        $course_id = $args['course_id'];
        $component = $args['component'];

        $stmt = $pdo->prepare("SELECT reason, status FROM grade_appeals WHERE stud_id = ? AND course_id = ? AND component = ?");
        $stmt->execute([$stud_id, $course_id, $component]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $response->getBody()->write(json_encode($result));
        } else {
            $response->getBody()->write(json_encode(['message' => 'No appeal found.']));
        }

        return $response->withHeader('Content-Type', 'application/json');
    });
};
