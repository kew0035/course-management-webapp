<?php
// namespace DAO;

// use PDO;

// class AdvisorDAO {
//     private $pdo;
//     private $advisorId;

//     public function __construct(PDO $pdo, $advisorId) {
//         $this->pdo = $pdo;
//         $this->advisorId = $advisorId;
//     }

//     public function getAdvisees() {
//         // 1. 根据 user_id 查出 advisor 的 adv_id
//         $stmt = $this->pdo->prepare("SELECT adv_id FROM advisors WHERE user_id = ?");
//         $stmt->execute([$this->advisorId]);
//         $advRow = $stmt->fetch(PDO::FETCH_ASSOC);

//         if (!$advRow) {
//             return []; // 用户不是顾问或没匹配到
//         }

//         $advId = $advRow['adv_id'];

//         // 2. 用 adv_id 查学生
//         $stmt = $this->pdo->prepare("SELECT * FROM students WHERE adv_id = ?");
//         $stmt->execute([$advId]);
//         return $stmt->fetchAll(PDO::FETCH_ASSOC);
//         }



//     public function getStudentCourses(int $studId): array {
//         $stmt = $this->pdo->prepare("
//             SELECT c.course_name,
//                    sg.continuous_total,
//                    sg.final_exam_score,
//                    sg.total_score,
//                    sg.grade
//             FROM student_grades sg
//             JOIN courses c ON sg.course_id = c.course_id
//             WHERE sg.stud_id = :studId
//         ");
//         $stmt->execute(['studId' => $studId]);
//         return $stmt->fetchAll(PDO::FETCH_ASSOC);
//     }

//     public function saveNote(int $studId, string $text): bool {
//         // Upsert note
//         $stmt = $this->pdo->prepare("
//             INSERT INTO advisor_notes(advisor_id, stud_id, note)
//             VALUES(:advId, :studId, :note)
//             ON DUPLICATE KEY UPDATE note = :note
//         ");
//         return $stmt->execute([
//             'advId'   => $this->advisorId,
//             'studId'  => $studId,
//             'note'    => $text
//         ]);
//     }

//     public function getConsultationReport(): array {
//         $stmt = $this->pdo->prepare("
//             SELECT s.matric_no, s.stud_name, s.gpa, n.note
//             FROM students s
//             LEFT JOIN advisor_notes n
//               ON s.stud_id = n.stud_id AND n.advisor_id = :advId
//             WHERE s.advisor_id = :advId
//         ");
//         $stmt->execute(['advId' => $this->advisorId]);
//         return $stmt->fetchAll(PDO::FETCH_ASSOC);
//     }

//     public function getNotesAndGPA() {
//         $sql = "
//             SELECT s.stud_name, s.matric_no,
//                    ROUND(AVG(g.total_score), 2) AS gpa,
//                    n.note
//             FROM students s
//             JOIN advisor_notes n ON n.stud_id = s.stud_id
//             LEFT JOIN student_grades g ON g.stud_id = s.stud_id
//             WHERE n.adv_id = (SELECT adv_id FROM advisors WHERE user_id = ?)
//             GROUP BY s.stud_id
//         ";
//         $stmt = $this->pdo->prepare($sql);
//         $stmt->execute([$this->userId]);
//         return $stmt->fetchAll(PDO::FETCH_ASSOC);
//     }
// }
namespace DAO;

use PDO;

class AdvisorDAO
{
    private $pdo;
    private $advisorId;

    public function __construct(PDO $pdo, ?int $advisorId = null)
    {
        $this->pdo = $pdo;
        $this->advisorId = $advisorId;
    }

    public function findAdvisorIdByUser(int $userId): ?int
    {
        $stmt = $this->pdo->prepare("SELECT adv_id FROM advisors WHERE user_id = ?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['adv_id'] : null;
    }
    public function findAdvisorProfileByUser(int $userId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT adv_id, adv_name AS adv_name FROM advisors WHERE user_id = ?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function getAdvisees(): array
    {
        // $stmt = $this->pdo->prepare("
        //     SELECT s.stud_id, s.stud_name, s.matric_no, s.gpa, n.note
        //     FROM students s
        //     LEFT JOIN advisor_notes n
        //       ON s.stud_id = n.stud_id AND n.adv_id = :advId
        //     WHERE s.advisor_id = :advId
        // ");
        // $stmt->execute(['advId' => $this->advisorId]);
        // return $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = $this->pdo->prepare("SELECT * FROM students WHERE adv_id = ?");
        $stmt->execute([$this->advisorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // public function getStudentCourses(int $studId): array {
    //     $stmt = $this->pdo->prepare("
    //         SELECT 
    //             c.course_name,
    //             scm.component,
    //             scm.score AS continuous_score,
    //             sg.final_exam_score,
    //             sg.total_score,
    //             sg.grade
    //         FROM student_courses sc
    //         JOIN courses c ON sc.course_id = c.course_id
    //         LEFT JOIN student_grades sg ON sg.course_id = c.course_id AND sg.stud_id = sc.stud_id
    //         LEFT JOIN student_continuous_marks scm ON scm.course_id = c.course_id AND scm.sg_id = sg.sg_id
    //         WHERE sc.stud_id = :studId
    //         ORDER BY c.course_name, scm.component
    //     ");
    //     $stmt->execute(['studId' => $studId]);
    //     $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    //     // 重组为每门课一项
    //     $courses = [];

    //     foreach ($rows as $row) {
    //         $courseName = $row['course_name'];

    //         if (!isset($courses[$courseName])) {
    //             $courses[$courseName] = [
    //                 'course_name' => $courseName,
    //                 'final_exam_score' => $row['final_exam_score'],
    //                 'total_score' => $row['total_score'],
    //                 'grade' => $row['grade'],
    //                 'components' => []
    //             ];
    //         }

    //         if (!empty($row['component'])) {
    //             $courses[$courseName]['components'][$row['component']] = (float)$row['continuous_score'];
    //         }
    //     }

    //     return array_values($courses); // reset numeric keys
    // }

    public function getStudentCourses(int $studId): array
    {
        $stmt = $this->pdo->prepare("
        SELECT 
            c.course_name,
            scm.component,
            scm.score AS continuous_score,
            sg.final_exam_score,
            sg.total_score,
            sg.grade
        FROM student_grades sg
        JOIN courses c ON sg.course_id = c.course_id
        LEFT JOIN student_continuous_marks scm ON scm.sg_id = sg.sg_id
        WHERE sg.stud_id = :studId
        ORDER BY c.course_name, scm.component
    ");
        $stmt->execute(['studId' => $studId]);

        $raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 分组每门课程，整理 component 到数组中
        $courses = [];
        foreach ($raw as $row) {
            $name = $row['course_name'];
            if (!isset($courses[$name])) {
                $courses[$name] = [
                    'course_name' => $name,
                    'final_exam_score' => $row['final_exam_score'],
                    'total_score' => $row['total_score'],
                    'grade' => $row['grade'],
                    'components' => []
                ];
            }
            if ($row['component']) {
                $courses[$name]['components'][$row['component']] = (float)$row['continuous_score'];
            }
        }

        return array_values($courses);
    }


    public function saveNote(int $studId, string $text): bool
    {
        // Upsert note
        $stmt = $this->pdo->prepare("
            INSERT INTO advisor_notes(adv_id, stud_id, note)
            VALUES(:advId, :studId, :note)
            ON DUPLICATE KEY UPDATE note = :note
        ");
        return $stmt->execute([
            'advId'   => $this->advisorId,
            'studId'  => $studId,
            'note'    => $text
        ]);
    }

    public function getNotesByStudent($studId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT note, created_at 
            FROM advisor_notes 
            WHERE adv_id = :advId AND stud_id = :studId 
            ORDER BY created_at DESC
        ");
        $stmt->execute([
            ':advId' => $this->advisorId,
            ':studId' => $studId
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getConsultationData()
    {
        // $stmt = $this->pdo->prepare("
        //     SELECT s.matric_no, s.stud_name, s.gpa, n.note
        //     FROM students s
        //     LEFT JOIN advisor_notes n ON s.stud_id = n.stud_id
        //     WHERE s.adv_id = ?
        //     ORDER BY s.matric_no
        // ");

        $stmt = $this->pdo->prepare("
            SELECT 
                s.matric_no, 
                s.stud_name, 
                s.gpa,
                GROUP_CONCAT(
                    CONCAT('[', DATE_FORMAT(sn.created_at, '%Y-%m-%d %H:%i'), '] ', sn.note)
                    ORDER BY sn.created_at SEPARATOR '\n---\n'
                ) AS note
            FROM students s
            LEFT JOIN advisor_notes sn ON sn.stud_id = s.stud_id
            WHERE s.adv_id = ?
            GROUP BY s.stud_id
        ");


        $stmt->execute([$this->advisorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
