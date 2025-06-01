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
    
    // public function addStudent(array $data): void {
    //     $this->dao->insert($data);
    // }
}