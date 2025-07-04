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
            throw $e; 
        }
    }

    public function getRanking($courseId) {
        // Fetch the student's total score for the given course
        $studId = $this->getStudentId();
    
        // Fetch the total score of the student for the course
        $sql = "
            SELECT total_score
            FROM student_grades 
            WHERE stud_id = :stud_id AND course_id = :course_id
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['stud_id' => $studId, 'course_id' => $courseId]);
    
        $studentTotalScore = $stmt->fetchColumn();
    
        // If the student doesn't have a score for the course, return a default empty value
        if ($studentTotalScore === false) {
            return [
                'rank' => 'No ranking data available.',
                'total_students' => 0
            ];
        }
    
        // Fetch the rank of the student (how many students have a higher score)
        $sqlRank = "
            SELECT COUNT(*) + 1 AS `rank`
            FROM student_grades sg1
            WHERE sg1.total_score > :student_score
            AND sg1.course_id = :course_id
        ";
        $stmtRank = $this->pdo->prepare($sqlRank);
        $stmtRank->execute(['student_score' => $studentTotalScore, 'course_id' => $courseId]);
        $rank = $stmtRank->fetchColumn();
    
        // Fetch the total number of students for the course
        $sqlCount = "
            SELECT COUNT(*) 
            FROM student_grades 
            WHERE course_id = :course_id
        ";
        $stmtCount = $this->pdo->prepare($sqlCount);
        $stmtCount->execute(['course_id' => $courseId]);
        $totalStudents = $stmtCount->fetchColumn();
    
        if ($totalStudents === 0) {
            return [
                'rank' => 0,
                'total_students' => 0
            ];
        }
    
        return [
            'rank' => (int)$rank,
            'total_students' => (int)$totalStudents
        ];
    }
    

    public function getPeers($courseId) {
        $sql = "
            SELECT
                sg.stud_id,
                s.user_id,
                CONCAT('Anon', LPAD(sg.sg_id, 3, '0')) AS id,
                ROUND(sg.total_score, 2) AS totalScore,
                RANK() OVER (ORDER BY sg.total_score DESC) AS `rank`
            FROM student_grades sg
            JOIN students s ON sg.stud_id = s.stud_id
            WHERE sg.course_id = :course_id
              AND sg.total_score IS NOT NULL
            ORDER BY sg.total_score DESC
            LIMIT 10
        ";
    
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['course_id' => $courseId]);
        $peers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        // Check if peers exist
        if (empty($peers)) {
            return [
                'message' => 'No peer comparison data available.'
            ];
        }
    
        return $peers;
    }

    public function getCourseIdByUserId($userId) {
        $sql = "
            SELECT course_id 
            FROM student_courses 
            WHERE stud_id = (
                SELECT stud_id 
                FROM students 
                WHERE user_id = :user_id 
                LIMIT 1
            )
            LIMIT 1
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $course = $stmt->fetch(PDO::FETCH_ASSOC);
        return $course ? $course['course_id'] : null;
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
                scm.scm_id,
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
                NULL AS scm_id,
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

public function getAppealByScmId(int $scmId, int $userId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT ga.status, ga.reason
            FROM grade_appeals ga
            JOIN student_continuous_marks scm ON ga.scm_id = scm.scm_id
            JOIN student_grades sg ON scm.sg_id = sg.sg_id
            JOIN students s ON sg.stud_id = s.stud_id
            WHERE ga.scm_id = ? AND s.user_id = ?
        ");
        $stmt->execute([$scmId, $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    public function isScmOwnedByUser($scm_id, $userId) {
        $stmt = $this->pdo->prepare("
            SELECT scm.scm_id
            FROM student_continuous_marks scm
            JOIN student_grades sg ON scm.sg_id = sg.sg_id
            JOIN students s ON sg.stud_id = s.stud_id
            WHERE scm.scm_id = ? AND s.user_id = ?
        ");
        $stmt->execute([$scm_id, $userId]);
        return $stmt->fetch() !== false;
    }

    public function appealExists($scm_id) {
        $stmt = $this->pdo->prepare("SELECT 1 FROM grade_appeals WHERE scm_id = ? LIMIT 1");
        $stmt->execute([$scm_id]);
        return $stmt->fetch() !== false;
    }

    public function submitAppeal($scm_id, $reason) {
        $stmt = $this->pdo->prepare("
            INSERT INTO grade_appeals (scm_id, reason, status) 
            VALUES (?, ?, 'pending')
        ");
        $stmt->execute([$scm_id, $reason]);
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

    public function getAdvisorNotes() {
        $studId = $this->getStudentId();

        $stmt = $this->pdo->prepare("
            SELECT 
                a.adv_id,
                a.adv_name AS advisor_name,
                a.email AS advisor_email,
                an.note,
                an.created_at,
                an.is_confidential
            FROM 
                advisor_notes an
            JOIN 
                advisors a ON an.adv_id = a.adv_id
            WHERE 
                an.stud_id = :stud_id
            ORDER BY 
                an.created_at DESC
        ");
        $stmt->execute(['stud_id' => $studId]);
        error_log("Fetching advisor notes for student ID: $studId");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}