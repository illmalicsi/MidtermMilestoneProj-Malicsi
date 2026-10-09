<?php
// classes/Comment.php - Recipe comment management and edit tracking

class Comment {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Fetch all comments for a recipe ordered chronologically
     */
    public function getByRecipeId(int $recipeId): array {
        $sql = "
            SELECT c.*, u.first_name, u.last_name
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.recipe_id = ?
            ORDER BY c.created_at ASC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$recipeId]);
        return $stmt->fetchAll();
    }

    /**
     * Fetch a specific comment by ID
     */
    public function getById(int $id): ?array {
        $stmt = $this->pdo->prepare("SELECT * FROM comments WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $comment = $stmt->fetch();
        return $comment ?: null;
    }

    /**
     * Add a comment to a recipe
     */
    public function create(int $recipeId, int $userId, string $body): bool {
        $sql = "
            INSERT INTO comments (recipe_id, user_id, body, created_at)
            VALUES (?, ?, ?, NOW())
        ";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$recipeId, $userId, trim($body)]);
    }

    /**
     * Update comment body and set updated_at timestamp to indicate edits
     */
    public function update(int $id, string $body): bool {
        $sql = "
            UPDATE comments
            SET body = ?, updated_at = NOW()
            WHERE id = ?
        ";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([trim($body), $id]);
    }

    /**
     * Delete a comment by ID
     */
    public function delete(int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM comments WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
