<?php

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
        $stmt = $this->pdo->prepare("SELECT * FROM students WHERE adv_id = ?");
        $stmt->execute([$this->advisorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


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
