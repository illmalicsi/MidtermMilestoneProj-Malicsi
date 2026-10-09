<?php
// classes/Favorite.php - Member recipe favorites and bookmarks

class Favorite {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Check if a recipe is saved in a user's personal favorites
     */
    public function isFavorite(int $userId, int $recipeId): bool {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ? AND recipe_id = ?");
        $stmt->execute([$userId, $recipeId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Toggle favorite: add if not favorited, delete if already favorited
     * Returns true if newly favorited, false if removed
     */
    public function toggle(int $userId, int $recipeId): bool {
        if ($this->isFavorite($userId, $recipeId)) {
            // Remove from favorites
            $stmt = $this->pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND recipe_id = ?");
            $stmt->execute([$userId, $recipeId]);
            return false;
        } else {
            // Add to favorites
            $stmt = $this->pdo->prepare("INSERT INTO favorites (user_id, recipe_id, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$userId, $recipeId]);
            return true;
        }
    }

    /**
     * Fetch all recipes favorited by a specific user ordered by latest saved
     */
    public function getUserFavorites(int $userId): array {
        $sql = "
            SELECT r.*, c.name AS category_name, u.first_name, u.last_name
            FROM favorites f
            JOIN recipes r ON f.recipe_id = r.id
            JOIN categories c ON r.category_id = c.id
            JOIN users u ON r.user_id = u.id
            WHERE f.user_id = ?
            ORDER BY f.created_at DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
