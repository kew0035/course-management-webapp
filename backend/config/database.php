<?php
if (!function_exists('getPDO')) {
    function getPDO() {
        $pdo = new PDO(
            'mysql:host=localhost;
            dbname=course_management;
            charset=utf8', 
            'root', 
            '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }
}
