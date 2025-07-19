<?php

namespace Service;

use DAO\LecturerDAO;

class LecturerService
{
    private $dao;

    public function __construct(LecturerDAO $dao)
    {
        $this->dao = $dao;
    }

    public function getCourseDetails(){
        return $this->dao->getCourseDetails();
    }
    public function getStudents()
    {
        return $this->dao->getStudentsByCourseId();
    }
    public function updateScores($matricNo, $continuousMarksJson, $finalExam)
    {
        return $this->dao->updateScores($matricNo, $continuousMarksJson, $finalExam);
    }
    public function getComponents()
    {
        return $this->dao->getComponentsByCourseId();
    }
    public function saveComponent($component, $maxMark, $weight, $originalName)
    {
        $weight = (int)$weight;
        return $this->dao->saveComponent( $component, $maxMark, $weight, $originalName);
    }
    public function deleteComponent($component)
    {
        return $this->dao->deleteComponent( $component);
    }
    public function syncStudentMarks()
    {
        return $this->dao->syncStudentMarks();
    }
}
