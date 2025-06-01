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
        $studId = $this->getStudentId();

        // 查询平时成绩组件
    $sql1 = "
        SELECT
            c.course_code,
            c.course_name,
            gw.component,
            COALESCE(scm.score, 0) AS score,
            gw.max_mark,
            gw.weight,
            0 AS sort_order
        FROM student_grades sg
        JOIN courses c ON sg.course_id = c.course_id
        JOIN grade_weights gw ON gw.course_id = sg.course_id
        LEFT JOIN student_continuous_marks scm ON scm.sg_id = sg.sg_id AND scm.component = gw.component
        WHERE sg.stud_id = ?
        AND gw.component != 'Final'
    ";

    $sql2 = "
        SELECT
            c.course_code,
            c.course_name,
            'Final Exam' AS component,
            sg.final_exam_score AS score,
            100 AS max_mark, 
            30 AS weight,
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

    // public function getRanking() {
    //     $studId = $this->getStudentId();

    //     // 计算排名示例，排名基于total_score降序
    //     $sqlRank = "
    //         SELECT COUNT(*) + 1 AS rank
    //         FROM student_grades sg1
    //         JOIN students s1 ON sg1.stud_id = s1.stud_id
    //         WHERE sg1.total_score > (
    //             SELECT total_score FROM student_grades sg2 WHERE sg2.stud_id = ?
    //         )
    //     ";
    //     $stmtRank = $this->pdo->prepare($sqlRank);
    //     $stmtRank->execute([$studId]);
    //     $rank = $stmtRank->fetchColumn();

    //     // 总人数
    //     $sqlCount = "SELECT COUNT(*) FROM student_grades";
    //     $total = $this->pdo->query($sqlCount)->fetchColumn();

    //     return [
    //         'rank' => (int)$rank,
    //         'total_students' => (int)$total
    //     ];
    // }

    // public function getPeers() {
    //     // 返回匿名同学数据，示例从 student_grades 中随机取部分学生数据
    //     $sql = "
    //         SELECT
    //             CONCAT('Anon', LPAD(sg.sg_id, 3, '0')) AS id,
    //             ROUND(sg.total_score, 2) AS totalScore,
    //             RANK() OVER (ORDER BY sg.total_score DESC) AS rank
    //         FROM student_grades sg
    //         ORDER BY sg.total_score DESC
    //         LIMIT 10
    //     ";
    //     $stmt = $this->pdo->prepare($sql);
    //     $stmt->execute();
    //     return $stmt->fetchAll(PDO::FETCH_ASSOC);
    // }

    // public function insert(array $data): void {
    //     $stmt = $this->pdo->prepare("INSERT INTO students (name, email, age) VALUES (:name, :email, :age)");
    //     $stmt->execute([
    //         ':name' => $data['name'],
    //         ':email' => $data['email'],
    //         ':age' => $data['age'] ?? null
    //     ]);
    // }
}