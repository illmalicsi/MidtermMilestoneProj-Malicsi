<?php
// classes/Category.php - Recipe categories database queries

class Category {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Fetch all categories ordered by ID
     */
    public function getAll(): array {
        $stmt = $this->pdo->query("SELECT * FROM categories ORDER BY id ASC");
        return $stmt->fetchAll();
    }

    /**
     * Fetch single category by ID
     */
    public function getById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
}
