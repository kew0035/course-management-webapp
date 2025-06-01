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

    public function addStudent(array $data): void {
        $this->dao->insert($data);
    }
}