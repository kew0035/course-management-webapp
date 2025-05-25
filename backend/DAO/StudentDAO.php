<?php
namespace DAO;

use PDO;

class StudentDAO {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function getAll(): array {
        $stmt = $this->pdo->query("SELECT * FROM students");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert(array $data): void {
        $stmt = $this->pdo->prepare("INSERT INTO students (name, email, age) VALUES (:name, :email, :age)");
        $stmt->execute([
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':age' => $data['age'] ?? null
        ]);
    }
}
