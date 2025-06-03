<?php
class LecturerDAO {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // public function getStudentsByCourseId($courseId) {
    //     $stmt = $this->pdo->prepare("
    //        SELECT 
    //             s.stud_id AS id,
    //             s.stud_name AS name,
    //             s.matric_no,
    //             sg.continuous_total,
    //             sg.final_exam_score,
    //             sg.total_score,
    //             JSON_OBJECTAGG(scm.component, scm.score) AS continuous_marks
    //         FROM students s
    //         JOIN student_grades sg ON s.stud_id = sg.stud_id
    //         LEFT JOIN student_continuous_marks scm ON sg.sg_id = scm.sg_id
    //         WHERE sg.course_id = :course_id
    //         GROUP BY s.stud_id

    //     ");
    //     $stmt->execute(['course_id' => $courseId]);
    //     return $stmt->fetchAll(PDO::FETCH_ASSOC);
    // }


    public function getStudentsByCourseId($courseId) {
    $stmt = $this->pdo->prepare("
        SELECT 
            s.stud_id AS id,
            s.stud_name AS name,
            s.matric_no,
            sg.sg_id,
            sg.continuous_total,
            sg.final_exam_score,
            sg.total_score,
            scm.component,
            scm.score
        FROM students s
        JOIN student_grades sg ON s.stud_id = sg.stud_id
        LEFT JOIN student_continuous_marks scm ON sg.sg_id = scm.sg_id
        WHERE sg.course_id = :course_id
    ");
    $stmt->execute(['course_id' => $courseId]);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $students = [];

    foreach ($rows as $row) {
        $id = $row['id'];

        if (!isset($students[$id])) {
            $students[$id] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'matric_no' => $row['matric_no'],
                'continuous_total' => $row['continuous_total'],
                'final_exam_score' => $row['final_exam_score'],
                'total_score' => $row['total_score'],
                'continuous_marks' => []
            ];
        }

        if ($row['component']) {
            $students[$id]['continuous_marks'][$row['component']] = $row['score'];
        }
    }

    return array_values($students); // 重排索引为数字数组
}

    public function updateScores($matricNo, $componentsJson, $finalExam, $courseId) {
        try {
            $this->pdo->beginTransaction();
    
            $stmt = $this->pdo->prepare("
                SELECT sg.sg_id AS id
                FROM student_grades sg
                JOIN students s ON sg.stud_id = s.stud_id
                WHERE s.matric_no = :matric_no 
                  AND sg.course_id = :course_id
            ");
            $stmt->execute([
                'matric_no' => $matricNo,
                'course_id' => $courseId
            ]);
            $grade = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$grade) {
                throw new Exception("Student grade record not found");
            }

            $components = json_decode($componentsJson, true);
            if (!is_array($components)) {
                throw new Exception("Invalid JSON for continuous_marks");
            }
    
            foreach ($components as $component => $score) {
                $stmt = $this->pdo->prepare("
                    INSERT INTO student_continuous_marks 
                        (sg_id, course_id, component, score)
                    VALUES 
                        (:grade_id, :course_id, :component, :score)
                    ON DUPLICATE KEY UPDATE 
                        score = VALUES(score)
                ");
                $stmt->execute([
                    'grade_id' => $grade['id'],
                    'course_id' => $courseId,
                    'component' => $component,
                    'score' => $score
                ]);
            }
    
            $stmt = $this->pdo->prepare("
                UPDATE student_grades 
                SET final_exam_score = :final_exam 
                WHERE sg_id = :grade_id
            ");
            $stmt->execute([
                'final_exam' => $finalExam,
                'grade_id' => $grade['id']
            ]);
    
            $this->pdo->commit();
            return true;
    
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Update failed: " . $e->getMessage());
            return false;
        }
    }
    
    public function getComponentsByCourseId($courseId) {
        $stmt = $this->pdo->prepare("SELECT * FROM grade_weights WHERE course_id = :course_id");
        $stmt->execute(['course_id' => $courseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function saveComponent($courseId, $component, $maxMark, $weightPercent) {
        try {
            $this->pdo->beginTransaction();
    
            $component = trim($component);
            $weight = (int)$weightPercent;
            $maxMark = (int)$maxMark;
    
            if ($component === '' || !is_numeric($weight) || $weight <= 0 || $weight > 100 || !is_numeric($maxMark) || $maxMark <= 0) {
                throw new InvalidArgumentException("Invalid component data");
            }

            $stmt = $this->pdo->prepare("
                SELECT SUM(weight) AS total 
                FROM grade_weights 
                WHERE course_id = :course_id
            ");
            $stmt->execute(['course_id' => $courseId]);
            $currentTotal = (int)$stmt->fetchColumn();
    
            $existing = $this->getComponent($courseId, $component);
            if ($existing) {
                $currentTotal -= (int)$existing['weight'];
            }
    
            if (($currentTotal + $weight) > 70) {
                throw new RuntimeException("Total weight cannot exceed 70%");
            }

            if ($existing) {
                $stmt = $this->pdo->prepare("
                    UPDATE grade_weights 
                    SET max_mark = :max_mark, weight = :weight 
                    WHERE gw_id = :id
                ");
                $stmt->execute([
                    'max_mark' => $maxMark,
                    'weight' => $weight,
                    'id' => $existing['gw_id']
                ]);
            } else {
                $stmt = $this->pdo->prepare("
                    INSERT INTO grade_weights 
                        (course_id, component, max_mark, weight) 
                    VALUES 
                        (:course_id, :component, :max_mark, :weight)
                ");
                $stmt->execute([
                    'course_id' => $courseId,
                    'component' => $component,
                    'max_mark' => $maxMark,
                    'weight' => $weight
                ]);
            }
    
            $this->pdo->commit();
            return true;
    
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("❌ Save component failed: " . $e->getMessage());
            throw $e;
        }
    }

    private function getComponent($courseId, $component) {
        $stmt = $this->pdo->prepare("
            SELECT * 
            FROM grade_weights 
            WHERE course_id = :course_id 
              AND component = :component
        ");
        $stmt->execute(['course_id' => $courseId, 'component' => $component]);
        return $stmt->fetch(PDO::FETCH_ASSOC); 
    }
    
    public function deleteComponent($courseId, $component) {
        try {
            $this->pdo->beginTransaction();
    
            $stmt1 = $this->pdo->prepare("
                DELETE scm FROM student_continuous_marks scm
                JOIN student_grades sg ON scm.sg_id = sg.sg_id
                WHERE sg.course_id = :course_id AND scm.component = :component
            ");
            $stmt1->execute([
                'course_id' => $courseId,
                'component' => $component
            ]);
    
            $stmt2 = $this->pdo->prepare("
                DELETE FROM grade_weights 
                WHERE course_id = :course_id AND component = :component
            ");
            $stmt2->execute([
                'course_id' => $courseId,
                'component' => $component
            ]);
    
            $this->pdo->commit();
            return true;
    
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("❌ Delete component failed: " . $e->getMessage());
            return false;
        }
    }
    
    public function syncStudentMarks($courseId) {
        try {
            $this->pdo->beginTransaction();
    
            $components = $this->getComponentsByCourseId($courseId);
    
            $stmt = $this->pdo->prepare("
                SELECT sg.sg_id AS grade_id
                FROM student_grades sg
                WHERE sg.course_id = :course_id
            ");
            $stmt->execute(['course_id' => $courseId]);
            $grades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            foreach ($grades as $grade) {
                foreach ($components as $component) {
                    $checkStmt = $this->pdo->prepare("
                        SELECT COUNT(*) FROM student_continuous_marks 
                        WHERE sg_id = :grade_id AND component = :component
                    ");
                    $checkStmt->execute([
                        'grade_id' => $grade['grade_id'],
                        'component' => $component['component']
                    ]);
    
                    if ($checkStmt->fetchColumn() == 0) {
                        $insertStmt = $this->pdo->prepare("
                            INSERT INTO student_continuous_marks 
                            (sg_id, course_id, component, score)
                            VALUES (:grade_id, :course_id, :component, 0)
                        ");
                        $insertStmt->execute([
                            'grade_id' => $grade['grade_id'],
                            'course_id' => $courseId,
                            'component' => $component['component']
                        ]);
                    }
                }
            }
            $this->pdo->commit();
            return true;
    
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("❌ Sync failed: " . $e->getMessage());
            return false;
        }
    }
    
    
}