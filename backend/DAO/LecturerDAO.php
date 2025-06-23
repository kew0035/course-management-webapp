<?php
class LecturerDAO {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

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

    // public function updateScores($matricNo, $componentsJson, $finalExam, $courseId) {
    //     try {
    //         $this->pdo->beginTransaction();

    //         // Step 1: Get sg_id
    //         $stmt = $this->pdo->prepare("
    //             SELECT sg.sg_id AS id
    //             FROM student_grades sg
    //             JOIN students s ON sg.stud_id = s.stud_id
    //             WHERE s.matric_no = :matric_no 
    //             AND sg.course_id = :course_id
    //         ");
    //         $stmt->execute([
    //             'matric_no' => $matricNo,
    //             'course_id' => $courseId
    //         ]);
    //         $grade = $stmt->fetch(PDO::FETCH_ASSOC);

    //         if (!$grade) {
    //             throw new Exception("Student grade record not found");
    //         }

    //         $components = json_decode($componentsJson, true);
    //         if (!is_array($components)) {
    //             throw new Exception("Invalid JSON for continuous_marks");
    //         }

    //         // Step 2: Fetch weights for course components
    //         $stmt = $this->pdo->prepare("
    //             SELECT component, max_mark, weight 
    //             FROM grade_weights 
    //             WHERE course_id = :course_id
    //         ");
    //         $stmt->execute(['course_id' => $courseId]);
    //         $weights = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //         $weightMap = [];
    //         foreach ($weights as $w) {
    //             $weightMap[$w['component']] = [
    //                 'max' => $w['max_mark'],
    //                 'weight' => $w['weight']
    //             ];
    //         }

    //         $totalWeightedScore = 0;

    //         // Step 3: Insert/update and compute weighted total
    //         foreach ($components as $component => $score) {
    //             $stmt = $this->pdo->prepare("
    //                 INSERT INTO student_continuous_marks 
    //                     (sg_id, course_id, component, score)
    //                 VALUES 
    //                     (:grade_id, :course_id, :component, :score)
    //                 ON DUPLICATE KEY UPDATE 
    //                     score = VALUES(score)
    //             ");
    //             $stmt->execute([
    //                 'grade_id' => $grade['id'],
    //                 'course_id' => $courseId,
    //                 'component' => $component,
    //                 'score' => $score
    //             ]);

    //             // Step 4: Compute weighted score
    //             if (isset($weightMap[$component])) {
    //                 $max = $weightMap[$component]['max'];
    //                 $weight = $weightMap[$component]['weight'];
    //                 if ($max > 0) {
    //                     $totalWeightedScore += ($score / $max) * $weight;
    //                 }
    //             }
    //         }

    //         $finalWeighted = $finalExam * 0.3;
    //         $totalScore = $finalWeighted + $totalWeightedScore;

    //         // echo $totalScore;
    //         // Step 5: Update continuous_total and final_exam
    //         $stmt = $this->pdo->prepare("
    //             UPDATE student_grades 
    //             SET final_exam_score = :final_exam,
    //                 continuous_total = :continuous_total,
    //                 total_score = :total_score
    //             WHERE sg_id = :grade_id
    //         ");
    //         $stmt->execute([
    //             'final_exam' => $finalExam,
    //             'continuous_total' => $totalWeightedScore,
    //             'total_score' => $totalScore,
    //             'grade_id' => $grade['id']
    //         ]);

    //         $this->pdo->commit();
    //         return true;

    //     } catch (Exception $e) {
    //         $this->pdo->rollBack();
    //         error_log("Update failed: " . $e->getMessage());
    //         return false;
    //     }
    // }
    public function updateScores($matricNo, $componentsJson, $finalExam, $courseId) {
        try {
            $this->pdo->beginTransaction();

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
    
    public function calculateGPA($studId): float {
        
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
                    continue; // 跳过未评分的课程，避免出错
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

    private function getGradeFromScore(float $score): string {
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

    private function getGradePointFromScore(float $score): float {
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