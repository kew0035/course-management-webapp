<?php
// LecturerService.php
require_once __DIR__ . '/../dao/LecturerDAO.php';

class LecturerService {
    private $dao;
    private $courseId = 1;

    public function __construct(LecturerDAO $dao) {
        $this->dao = $dao;
    }
    public function getStudents() {
        return $this->dao->getStudentsByCourseId($this->courseId);
    }
    public function updateScores($matricNo, $continuousMarksJson, $finalExam) {
        return $this->dao->updateScores($matricNo, $continuousMarksJson, $finalExam, $this->courseId);
    }
    public function getComponents() {
        return $this->dao->getComponentsByCourseId($this->courseId);
    }
    public function saveComponent($component, $maxMark, $weight) {
        $weight = (int)$weight;
        return $this->dao->saveComponent($this->courseId, $component, $maxMark, $weight);
    }
    public function deleteComponent($component) {
        return $this->dao->deleteComponent($this->courseId, $component);
    }
    public function syncStudentMarks() {
        return $this->dao->syncStudentMarks($this->courseId);
    }
}