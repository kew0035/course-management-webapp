<?php
namespace Service;

use DAO\StudentDAO;

class StudentService {
    private $dao;

    public function __construct(StudentDAO $dao) {
        $this->dao = $dao;
    }

    public function listStudents(): array {
        return $this->dao->getAll();
    }

    public function getGrades() {
        return $this->dao->getGrades();
    }

    public function getRanking() {
        return $this->dao->getRanking();
    }

    public function getPeers() {
        return $this->dao->getPeers();
    }
    
    public function getCourses() {
        return $this->dao->getCourses();
    }
    public function getGradesByCourse($courseId) {
        return $this->dao->getGradesByCourse($courseId);
    }


    public function calculateGPAById(): float {
        $studId = $this->dao->getStudentId();
        return $this->dao->calculateGPA($studId);
    }

    public function getAdvisorNotes() {
        return $this->dao->getAdvisorNotes();
    }


}