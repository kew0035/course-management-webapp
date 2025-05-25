<?php
if (!function_exists('getPDO')) {
    function getPDO() {
        $pdo = new PDO(
            'mysql:host=localhost;
            dbname=course_management;
            charset=utf8', 
            'root', 
            'ip_password');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }
}
