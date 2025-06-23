<?php
namespace DAO;

use PDO;
use Exception;

class StudentDAO {
    private $pdo;
    private $userId;

    public function __construct(PDO $pdo, int $userId = 0) {
        $this->pdo = $pdo;
        $this->userId = $userId;
    }

    public function getAll(): array {
        $stmt = $this->pdo->query("SELECT * FROM students");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStudentId() {
        $stmt = $this->pdo->prepare("SELECT stud_id FROM students WHERE user_id = ?");
        $stmt->execute([$this->userId]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$student) {
            throw new Exception("Student not found");
        }
        return $student['stud_id'];
    }

    public function getGrades() {
        try{
            $studId = $this->getStudentId();

            // Continuous Assessment
            $sql1 = "
            SELECT
                c.course_code,
                c.course_name,
                gw.component,
                COALESCE(scm.score, 0) AS score,
                gw.max_mark,
                gw.weight,
                sg.course_id,
                0 AS sort_order
            FROM student_grades sg
            JOIN courses c ON sg.course_id = c.course_id
            JOIN grade_weights gw ON gw.course_id = sg.course_id
            LEFT JOIN student_continuous_marks scm ON scm.sg_id = sg.sg_id AND scm.component = gw.component
            WHERE sg.stud_id = ?
            AND gw.component != 'Final'
        ";
        // Final
        $sql2 = "
            SELECT
                c.course_code,
                c.course_name,
                'Final Exam' AS component,
                sg.final_exam_score AS score,
                100 AS max_mark, 
                30 AS weight,
                sg.course_id,
                1 AS sort_order
            FROM student_grades sg
            JOIN courses c ON sg.course_id = c.course_id
            WHERE sg.stud_id = ?
        ";

            $sql = "($sql1) UNION ALL ($sql2) ORDER BY course_code, sort_order, component";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$studId, $studId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(Exception $e){
            error_log("getGrades error: " . $e->getMessage());
            throw $e;  // 或 return 空数组来避免 500
        }
    }

    public function getRanking() {
        $studId = $this->getStudentId();

        // Calculate ranking example, ranking based on total_score in descending order
        $sqlRank = "
            SELECT COUNT(*) + 1 AS `rank`
            FROM student_grades sg1
            WHERE sg1.total_score > (
                SELECT total_score FROM student_grades sg2 WHERE sg2.stud_id = ?
            )
        ";
        $stmtRank = $this->pdo->prepare($sqlRank);
        $stmtRank->execute([$studId]);
        $rank = $stmtRank->fetchColumn();

        // total people
        $sqlCount = "SELECT COUNT(*) FROM student_grades";
        $total = $this->pdo->query($sqlCount)->fetchColumn();

        return [
            'rank' => (int)$rank,
            'total_students' => (int)$total
        ];
    }

    public function getPeers() {
        // Return anonymous classmates data. This example randomly selects some student data from student_grades.
        $sql = "
            SELECT
                sg.stud_id,
                s.user_id,
                CONCAT('Anon', LPAD(sg.sg_id, 3, '0')) AS id,
                ROUND(sg.total_score, 2) AS totalScore,
                RANK() OVER (ORDER BY sg.total_score DESC) AS `rank`
            FROM student_grades sg
            JOIN students s ON sg.stud_id = s.stud_id
            ORDER BY sg.total_score DESC
            LIMIT 10
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    }
    
    // public function insert(array $data): void {
    //     $stmt = $this->pdo->prepare("INSERT INTO students (name, email, age) VALUES (:name, :email, :age)");
    //     $stmt->execute([
    //         ':name' => $data['name'],
    //         ':email' => $data['email'],
    //         ':age' => $data['age'] ?? null
    //     ]);
    // }

    public function getCourses() {
    $studId = $this->getStudentId();

    $sql = "
        SELECT c.course_id, c.course_code, c.course_name
        FROM courses c
        JOIN student_courses sc ON sc.course_id = c.course_id
        WHERE sc.stud_id = ?
    ";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([$studId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function getGradesByCourse($courseId) {
    try {
        $studId = $this->getStudentId();

        // Continuous Assessment
        $sql1 = "
            SELECT
                c.course_code,
                c.course_name,
                gw.component,
                COALESCE(scm.score, 0) AS score,
                gw.max_mark,
                gw.weight,
                sg.course_id,
                0 AS sort_order
            FROM student_grades sg
            JOIN courses c ON sg.course_id = c.course_id
            JOIN grade_weights gw ON gw.course_id = sg.course_id
            LEFT JOIN student_continuous_marks scm ON scm.sg_id = sg.sg_id AND scm.component = gw.component
            WHERE sg.stud_id = ? AND sg.course_id = ? AND gw.component != 'Final'
        ";

        // Final Exam
        $sql2 = "
            SELECT
                c.course_code,
                c.course_name,
                'Final Exam' AS component,
                sg.final_exam_score AS score,
                100 AS max_mark, 
                30 AS weight,
                sg.course_id,
                1 AS sort_order
            FROM student_grades sg
            JOIN courses c ON sg.course_id = c.course_id
            WHERE sg.stud_id = ? AND sg.course_id = ?
        ";

        $sql = "($sql1) UNION ALL ($sql2) ORDER BY course_code, sort_order, component";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$studId, $courseId, $studId, $courseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("getGradesByCourse error: " . $e->getMessage());
        throw $e;
    }
}

// public function calculateGPA($studId): float {
//     $stmt = $this->pdo->prepare("
//         SELECT sg.sg_id, sg.total_score, c.credit
//         FROM student_grades sg
//         JOIN courses c ON sg.course_id = c.course_id
//         WHERE sg.stud_id = ?
//     ");
//     $stmt->execute([$studId]);
//     $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

//     $totalQualityPoints = 0;
//     $totalCredits = 0;

//     foreach ($results as $r) {
//         $score = $r['total_score'];
//         $credit = $r['credit'];
//         $gradePoint = $this->getGradePointFromScore($score);
//         $totalQualityPoints += $gradePoint * $credit;
//         $totalCredits += $credit;

//         $stmtGrade = $this->pdo->prepare("UPDATE student_grades SET grade = :grade WHERE sg_id = :id");
//         $stmtGrade->execute([
//             'grade' => $this->getGradeFromScore($score),
//             'id' => $r['sg_id']
//         ]);
//     }

//     $gpa = $totalCredits > 0 ? round($totalQualityPoints / $totalCredits, 2) : 0.00;

//     $stmtUpdate = $this->pdo->prepare("UPDATE students SET gpa = :gpa WHERE stud_id = :id");
//     $stmtUpdate->execute(['gpa' => $gpa, 'id' => $studId]);

//     return $gpa;
// }

// private function getGradeFromScore(float $score): string {
//     if ($score >= 90) return 'A+';
//     if ($score >= 80) return 'A';
//     if ($score >= 75) return 'A-';
//     if ($score >= 70) return 'B+';
//     if ($score >= 65) return 'B';
//     if ($score >= 60) return 'B-';
//     if ($score >= 55) return 'C+';
//     if ($score >= 50) return 'C';
//     if ($score >= 45) return 'C-';
//     if ($score >= 40) return 'D+';
//     if ($score >= 35) return 'D';
//     if ($score >= 30) return 'D-';
//     return 'F';
// }

// private function getGradePointFromScore(float $score): float {
//     return match (true) {
//         $score >= 90 => 4.00,
//         $score >= 80 => 4.00,
//         $score >= 75 => 3.67,
//         $score >= 70 => 3.33,
//         $score >= 65 => 3.00,
//         $score >= 60 => 2.67,
//         $score >= 55 => 2.33,
//         $score >= 50 => 2.00,
//         $score >= 45 => 1.67,
//         $score >= 40 => 1.33,
//         $score >= 35 => 1.00,
//         $score >= 30 => 0.67,
//         default => 0.00
//     };
// }

    public function getStudentIdByMatric($matricNo) {
        $stmt = $this->pdo->prepare("SELECT stud_id FROM students WHERE matric_no = ?");
        $stmt->execute([$matricNo]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['stud_id'] : null;
    }

    public function getAllStudentsByCourse($courseId) {
        $stmt = $this->pdo->prepare("SELECT * FROM students WHERE course_id = ?");
        $stmt->execute([$courseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }   
}