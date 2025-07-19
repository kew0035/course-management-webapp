<?php

namespace Service;

use DAO\StudentDAO;

class StudentService
{
    private $dao;

    public function __construct(StudentDAO $dao)
    {
        $this->dao = $dao;
    }

    public function listStudents(): array
    {
        return $this->dao->getAll();
    }

    public function getGrades()
    {
        return $this->dao->getGrades();
    }

    public function getRanking($courseId)
    {
        return $this->dao->getRanking($courseId);
    }

    public function getPeers($courseId)
    {
        return $this->dao->getPeers($courseId);
    }

    public function getCourses()
    {
        return $this->dao->getCourses();
    }
    public function getGradesByCourse($courseId)
    {
        return $this->dao->getGradesByCourse($courseId);
    }

    // public function calculateGPAById(): float {
    //     $studId = $this->dao->getStudentId();
    //     return $this->dao->calculateGPA($studId);
    // }

    public function getAdvisorNotes()
    {
        return $this->dao->getAdvisorNotes();
    }

    public function getAppealByScmId($scmId, $courseId, $userId): ?array
    {
        return $this->dao->getAppealByScmId($scmId, $courseId, $userId) ?: [];
    }

    public function submitAppeal($scm_id, $course_id, $reason, $userId)
    {
        if (!$scm_id || !$reason) {
            return ['status' => 400, 'message' => 'Missing required fields: scm_id and reason.'];
        }

        if (!$this->dao->isScmOwnedByUser($scm_id, $userId)) {
            return ['status' => 403, 'message' => 'Invalid scm_id or unauthorized access.'];
        }

        if ($this->dao->appealExists($scm_id, $course_id)) {
            return ['status' => 409, 'message' => 'Appeal already submitted for this component.'];
        }

        $this->dao->submitAppeal($scm_id, $reason);
        
        return ['status' => 200, 'message' => 'Appeal submitted successfully.'];
    }
}
