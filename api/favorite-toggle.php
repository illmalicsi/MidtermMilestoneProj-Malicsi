<?php
// api/favorite-toggle.php - Asynchronous favorite toggle endpoint (Zero-reload AJAX)
header('Content-Type: application/json');

require_once '../db.php';
require_once '../classes/Auth.php';
require_once '../classes/Favorite.php';

Auth::start();

// Block unauthenticated requests
if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please log in to save favorites.']);
    exit;
}

// Read JSON request payload
$input = json_decode(file_get_contents('php://input'), true);
$recipeId = !empty($input['recipe_id']) ? (int)$input['recipe_id'] : (!empty($_POST['recipe_id']) ? (int)$_POST['recipe_id'] : 0);

if ($recipeId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid recipe ID.']);
    exit;
}

// Toggle favorite in database
$favoriteModel = new Favorite($pdo);
$isFavorite = $favoriteModel->toggle(Auth::id(), $recipeId);

if ($isFavorite === null) {
    echo json_encode(['success' => false, 'message' => 'Recipe not found.']);
    exit;
}

// Return JSON response to client
echo json_encode([
    'success' => true,
    'is_favorite' => $isFavorite
]);
