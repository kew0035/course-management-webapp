<?php

namespace DAO;

use PDO;
use Exception;
use InvalidArgumentException;
use RuntimeException;

class LecturerDAO
{
    private $pdo;
    private $userId;

    public function __construct(PDO $pdo,  int $userId = 0)
    {
        $this->pdo = $pdo;
        $this->userId = $userId;
    }
    // get lecturer id
    public function getLecturerId()
    {
        $stmt = $this->pdo->prepare("SELECT lec_id FROM lecturers WHERE user_id = ?");
        $stmt->execute([$this->userId]);
        $lecturer = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$lecturer) {
            throw new Exception("Lecturer not found");
        }
        return $lecturer['lec_id'];
    }

    //get course id based on lecturer id
    public function getCourseIdBasedOnLecId()
    {
        $lecId = $this->getLecturerId();
        $stmt = $this->pdo->prepare("SELECT course_id FROM courses WHERE lec_id = ?");
        $stmt->execute([$lecId]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$course) {
            throw new Exception("Course not found");
        }
        return $course['course_id'];
    }

    //get course name
    public function getCourseDetails()
    {
        $courseId = $this->getCourseIdBasedOnLecId();
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM courses 
            WHERE course_id = :course_id
        ");
        $stmt->execute(['course_id' => $courseId]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$course) {
            throw new Exception("Course not found");
        }
        return $course;
    }

    //end get course name
    public function getStudentsByCourseId()
    {
        $lecId = $this->getLecturerId();
        $courseId = $this->getCourseIdBasedOnLecId();
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
            JOIN courses c ON sg.course_id = c.course_id
            WHERE sg.course_id = :course_id AND c.lec_id = :lec_id
        ");
        $stmt->execute(['course_id' => $courseId, 'lec_id' => $lecId]);

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

        return array_values($students);
    }
    public function updateScores($matricNo, $componentsJson, $finalExam)
    {
        try {
            $this->pdo->beginTransaction();
            $courseId = $this->getCourseIdBasedOnLecId();

            // Step 1: Get sg_id and stud_id
            $stmt = $this->pdo->prepare("
                SELECT sg.sg_id AS id, s.stud_id
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

            $studId = $grade['stud_id'];
            $components = json_decode($componentsJson, true);
            if (!is_array($components)) {
                throw new Exception("Invalid JSON for continuous_marks");
            }

            // Step 2: Get component weights
            $stmt = $this->pdo->prepare("
                SELECT component, max_mark, weight 
                FROM grade_weights 
                WHERE course_id = :course_id
            ");
            $stmt->execute(['course_id' => $courseId]);
            $weights = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $weightMap = [];
            foreach ($weights as $w) {
                $weightMap[$w['component']] = [
                    'max' => $w['max_mark'],
                    'weight' => $w['weight']
                ];
            }

            $totalWeightedScore = 0;

            // Step 3: Update continuous marks
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

                if (isset($weightMap[$component])) {
                    $max = $weightMap[$component]['max'];
                    $weight = $weightMap[$component]['weight'];
                    if ($max > 0) {
                        $totalWeightedScore += ($score / $max) * $weight;
                    }
                }
            }

            $finalWeighted = $finalExam * 0.3;
            $totalScore = $totalWeightedScore + $finalWeighted;

            // Step 4: Update total scores
            $stmt = $this->pdo->prepare("
                UPDATE student_grades 
                SET final_exam_score = :final_exam,
                    continuous_total = :continuous_total,
                    total_score = :total_score
                WHERE sg_id = :grade_id
            ");
            $stmt->execute([
                'final_exam' => $finalExam,
                'continuous_total' => $totalWeightedScore,
                'total_score' => $totalScore,
                'grade_id' => $grade['id']
            ]);

            // Step 5: Recalculate GPA
            $this->calculateGPA($studId);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("Update failed: " . $e->getMessage());
            return false;
        }
    }

    public function getComponentsByCourseId()
    {
        $courseId = $this->getCourseIdBasedOnLecId();
        $stmt = $this->pdo->prepare("SELECT * FROM grade_weights WHERE course_id = :course_id");
        $stmt->execute(['course_id' => $courseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function saveComponent($component, $maxMark, $weightPercent, $originalName = null)
    {
        try {
            $this->pdo->beginTransaction();

            $component = trim($component);
            $originalName = trim($originalName ?? $component); // fallback if null
            $weight = (int)$weightPercent;
            $maxMark = (int)$maxMark;
            $courseId = $this->getCourseIdBasedOnLecId();

            error_log("🛠️ SaveComponent invoked");
            error_log("👉 originalName: $originalName | new name: $component | maxMark: $maxMark | weight: $weight");

            if ($component === '' || !is_numeric($weight) || $weight <= 0 || $weight > 100 || !is_numeric($maxMark) || $maxMark <= 0) {
                throw new InvalidArgumentException("Invalid component data");
            }

            // Check if component exists (using original name)
            $existing = $this->getComponent($originalName);
            if ($existing) {
                $currentTotalStmt = $this->pdo->prepare("
                    SELECT SUM(weight) AS total FROM grade_weights 
                    WHERE course_id = :course_id AND component != :original_component
                ");
                $currentTotalStmt->execute([
                    'course_id' => $courseId,
                    'original_component' => $originalName
                ]);
            } else {
                $currentTotalStmt = $this->pdo->prepare("
                    SELECT SUM(weight) AS total FROM grade_weights 
                    WHERE course_id = :course_id
                ");
                $currentTotalStmt->execute(['course_id' => $courseId]);
            }

            $currentTotal = (int)$currentTotalStmt->fetchColumn();

            if (($currentTotal + $weight) > 70) {
                throw new RuntimeException("Total weight cannot exceed 70%");
            }

            // Update case
            if ($existing) {
                // Now update grade_weights
                $stmt = $this->pdo->prepare("
                    UPDATE grade_weights 
                    SET component = :new_component, max_mark = :max_mark, weight = :weight 
                    WHERE gw_id = :gw_id
                ");
                $stmt->execute([
                    'new_component' => $component,
                    'max_mark' => $maxMark,
                    'weight' => $weight,
                    'gw_id' => $existing['gw_id']
                ]);

                // First update student_continuous_marks table if name is changed
                if ($originalName !== $component) {
                    $syncStmt = $this->pdo->prepare("
                        UPDATE student_continuous_marks 
                        SET component = :new_component 
                        WHERE course_id = :course_id AND component = :original_component
                    ");
                    $syncStmt->execute([
                        'new_component' => $component,
                        'course_id' => $courseId,
                        'original_component' => $originalName
                    ]);
                }
            } else {
                // Insert new component
                $stmt = $this->pdo->prepare("
                    INSERT INTO grade_weights 
                    (course_id, component, max_mark, weight) 
                    VALUES (:course_id, :component, :max_mark, :weight)
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

    private function getComponent($component)
    {
        $courseId = $this->getCourseIdBasedOnLecId();
        $stmt = $this->pdo->prepare("
            SELECT * 
            FROM grade_weights 
            WHERE course_id = :course_id 
              AND component = :component
        ");
        $stmt->execute(['course_id' => $courseId, 'component' => $component]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteComponent($component)
    {
        try {
            $this->pdo->beginTransaction();
            $courseId = $this->getCourseIdBasedOnLecId();

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

    public function syncStudentMarks()
    {
        try {
            $this->pdo->beginTransaction();
            $courseId = $this->getCourseIdBasedOnLecId();
            $components = $this->getComponentsByCourseId();

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

    public function calculateGPA($studId): float
    {

        $stmt = $this->pdo->prepare("
            SELECT sg.sg_id, sg.total_score, c.credit
            FROM student_grades sg
            JOIN courses c ON sg.course_id = c.course_id
            WHERE sg.stud_id = ?
        ");
        $stmt->execute([$studId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalQualityPoints = 0;
        $totalCredits = 0;

        foreach ($results as $r) {
            $score = $r['total_score'];
            if ($score === null) {
                continue;
            }
            $credit = $r['credit'];
            $gradePoint = $this->getGradePointFromScore($score);
            $totalQualityPoints += $gradePoint * $credit;
            $totalCredits += $credit;

            $stmtGrade = $this->pdo->prepare("UPDATE student_grades SET grade = :grade WHERE sg_id = :id");
            $stmtGrade->execute([
                'grade' => $this->getGradeFromScore($score),
                'id' => $r['sg_id']
            ]);
        }

        $gpa = $totalCredits > 0 ? round($totalQualityPoints / $totalCredits, 2) : 0.00;

        $stmtUpdate = $this->pdo->prepare("UPDATE students SET gpa = :gpa WHERE stud_id = :id");
        $stmtUpdate->execute(['gpa' => $gpa, 'id' => $studId]);

        return $gpa;
    }

    private function getGradeFromScore(float $score): string
    {
        if ($score >= 90) return 'A+';
        if ($score >= 80) return 'A';
        if ($score >= 75) return 'A-';
        if ($score >= 70) return 'B+';
        if ($score >= 65) return 'B';
        if ($score >= 60) return 'B-';
        if ($score >= 55) return 'C+';
        if ($score >= 50) return 'C';
        if ($score >= 45) return 'C-';
        if ($score >= 40) return 'D+';
        if ($score >= 35) return 'D';
        if ($score >= 30) return 'D-';
        return 'F';
    }

    private function getGradePointFromScore(float $score): float
    {
        return match (true) {
            $score >= 90 => 4.00,
            $score >= 80 => 4.00,
            $score >= 75 => 3.67,
            $score >= 70 => 3.33,
            $score >= 65 => 3.00,
            $score >= 60 => 2.67,
            $score >= 55 => 2.33,
            $score >= 50 => 2.00,
            $score >= 45 => 1.67,
            $score >= 40 => 1.33,
            $score >= 35 => 1.00,
            $score >= 30 => 0.67,
            default => 0.00
        };
    }
}
