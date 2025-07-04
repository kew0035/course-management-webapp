<?php
// src/Pages/LoginPage.php
namespace App\backend;

use Psr\Http\Message\ResponseInterface as Response;

class LoginPage
{
    public static function handleLogin($pdo, $requestData): array
    {
        $username = $requestData['username'] ?? '';
        $password = $requestData['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            return ['status' => 401, 'message' => 'Username does not exist'];
        }

        if ($password !== $user['password']) {
            return ['status' => 401, 'data' => [
                'message' => 'Password Error'
            ]];
        }

        $roleTableMap = [
            'lecturer' => [
                'table' => 'lecturers',
                'name_field' => 'lec_name'
            ],
            'student' => [
                'table' => 'students',
                'name_field' => 'stud_name'
            ],
            'advisor' => [
                'table' => 'advisors',
                'name_field' => 'adv_name'
            ]
        ];

        $userName = $username;
        $role = strtolower($user['role']);

        if (isset($roleTableMap[$role])) {
            $config = $roleTableMap[$role];
            try {
                $stmt = $pdo->prepare("SELECT {$config['name_field']} 
                                     FROM {$config['table']} 
                                     WHERE user_id = :user_id 
                                     LIMIT 1");
                $stmt->execute(['user_id' => $user['user_id']]);
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($result && !empty($result[$config['name_field']])) {
                    $userName = $result[$config['name_field']];
                }
            } catch (\PDOException $e) {
                error_log("Name query failed: " . $e->getMessage());
            }
        }
        return [
            'status' => 200,
            'data' => [
                'message' => 'Login Successful',
                'user_id' => $user['user_id'],
                'username' => $user['username'],
                'role' => $user['role'],
                'name' => $userName
            ]
        ];
    }
}
