<?php
// classes/Recipe.php - Recipe CRUD, search, and filtering operations

class Recipe {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Get latest recipes first, with optional keyword search and category filter
     */
    public function getAll(string $search = '', ?int $categoryId = null): array {
        $sql = "
            SELECT r.*, c.name AS category_name, u.first_name, u.last_name
            FROM recipes r
            JOIN categories c ON r.category_id = c.id
            JOIN users u ON r.user_id = u.id
            WHERE 1=1
        ";
        $params = [];

        // Search by keyword in title or description
        if (!empty($search)) {
            $sql .= " AND (r.title LIKE ? OR r.description LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        // Narrow down by category
        if (!empty($categoryId)) {
            $sql .= " AND r.category_id = ?";
            $params[] = $categoryId;
        }

        // Always show latest recipes first
        $sql .= " ORDER BY r.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get a single recipe by its ID with author and category details
     */
    public function getById(int $id): ?array {
        $sql = "
            SELECT r.*, c.name AS category_name, u.first_name, u.last_name
            FROM recipes r
            JOIN categories c ON r.category_id = c.id
            JOIN users u ON r.user_id = u.id
            WHERE r.id = ?
            LIMIT 1
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $recipe = $stmt->fetch();
        return $recipe ?: null;
    }

    /**
     * Insert a new recipe record into the database
     */
    public function create(int $userId, int $categoryId, string $title, string $description, string $steps): int|false {
        $sql = "
            INSERT INTO recipes (user_id, category_id, title, description, steps, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ";
        $stmt = $this->pdo->prepare($sql);
        $success = $stmt->execute([$userId, $categoryId, trim($title), trim($description), trim($steps)]);
        return $success ? (int)$this->pdo->lastInsertId() : false;
    }

    /**
     * Update an existing recipe and set updated_at timestamp to indicate edits
     */
    public function update(int $id, int $categoryId, string $title, string $description, string $steps): bool {
        $sql = "
            UPDATE recipes
            SET category_id = ?, title = ?, description = ?, steps = ?, updated_at = NOW()
            WHERE id = ?
        ";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$categoryId, trim($title), trim($description), trim($steps), $id]);
    }

    /**
     * Delete a recipe and its associated ingredients, comments, and favorites
     */
    public function delete(int $id): bool {
        // Cascade delete related records
        $this->pdo->prepare("DELETE FROM ingredients WHERE recipe_id = ?")->execute([$id]);
        $this->pdo->prepare("DELETE FROM comments WHERE recipe_id = ?")->execute([$id]);
        $this->pdo->prepare("DELETE FROM favorites WHERE recipe_id = ?")->execute([$id]);

        $stmt = $this->pdo->prepare("DELETE FROM recipes WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
