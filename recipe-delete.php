<?php
// recipe-delete.php - Recipe deletion handler with author ownership check

require_once 'db.php';
require_once 'includes/helpers.php';
require_once 'classes/Auth.php';
require_once 'classes/Recipe.php';

// Enforce member access
Auth::requireLogin();

$recipeId = !empty($_GET['id']) ? (int)$_GET['id'] : 0;
$recipeModel = new Recipe($pdo);
$recipe = $recipeModel->getById($recipeId);

// Strictly allow only the author to delete
if ($recipe && (int)$recipe['user_id'] === Auth::id()) {
    $recipeModel->delete($recipeId);
    setFlash('success', 'Recipe was successfully deleted.');
} else {
    setFlash('error', 'You are not authorized to delete this recipe.');
}

// Redirect back to home feed
header('Location: index.php');
exit;
