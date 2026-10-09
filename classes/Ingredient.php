<?php
// classes/Ingredient.php - Recipe ingredients database operations

class Ingredient {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Fetch all ingredients for a specific recipe
     */
    public function getByRecipeId(int $recipeId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM ingredients WHERE recipe_id = ? ORDER BY id ASC");
        $stmt->execute([$recipeId]);
        return $stmt->fetchAll();
    }

    /**
     * Add a single ingredient for a recipe
     */
    public function add(int $recipeId, string $name): bool {
        $stmt = $this->pdo->prepare("INSERT INTO ingredients (recipe_id, name) VALUES (?, ?)");
        return $stmt->execute([$recipeId, trim($name)]);
    }

    /**
     * Delete all ingredients for a recipe (used when updating recipe)
     */
    public function deleteByRecipeId(int $recipeId): bool {
        $stmt = $this->pdo->prepare("DELETE FROM ingredients WHERE recipe_id = ?");
        return $stmt->execute([$recipeId]);
    }
}
