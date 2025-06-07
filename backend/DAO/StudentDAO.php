<?php
namespace DAO;

use PDO;
use Exception;

class StudentDAO {
    private $pdo;
    private $userId;

    public function __construct(PDO $pdo, int $userId) {
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


}