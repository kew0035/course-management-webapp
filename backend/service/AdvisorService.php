<?php

namespace Service;

use DAO\AdvisorDAO;

class AdvisorService
{
    private AdvisorDAO $dao;

    public function __construct(AdvisorDAO $dao)
    {
        $this->dao = $dao;
    }

    public function getAdvisorIdByUser(int $userId): ?int
    {
        return $this->dao->findAdvisorIdByUser($userId);
    }

    public function getAdvisorProfileByUser(int $userId): ?array
    {
        return $this->dao->findAdvisorProfileByUser($userId);
    }


    public function getAdvisees(): array
    {
        return $this->dao->getAdvisees();
    }

    public function getStudentDetail(int $studId): array
    {
        return ['courses' => $this->dao->getStudentCourses($studId)];
    }

    public function saveNote(int $studId, string $note): bool
    {
        return $this->dao->saveNote($studId, $note);
    }

    public function getNotesByStudent($studId): array
    {
        return $this->dao->getNotesByStudent($studId);
    }

    public function getConsultationReport()
    {
        return $this->dao->getConsultationData();
    }
}
