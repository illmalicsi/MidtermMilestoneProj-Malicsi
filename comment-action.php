<?php
// comment-action.php - Handle comment creation and deletion

require_once 'db.php';
require_once 'includes/helpers.php';
require_once 'classes/Auth.php';
require_once 'classes/Comment.php';

// Enforce member access
Auth::requireLogin();

$commentModel = new Comment($pdo);
$userId = Auth::id();

$action = $_POST['action'] ?? ($_GET['action'] ?? '');
$recipeId = !empty($_POST['recipe_id']) ? (int)$_POST['recipe_id'] : (!empty($_GET['recipe_id']) ? (int)$_GET['recipe_id'] : 0);

// Create comment
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = trim($_POST['body'] ?? '');
    if (!empty($body) && $recipeId > 0) {
        $commentModel->create($recipeId, $userId, $body);
        setFlash('success', 'Your comment has been posted!');
    } else {
        setFlash('error', 'Comment cannot be blank.');
    }
// Delete comment
} elseif ($action === 'delete') {
    $commentId = !empty($_GET['id']) ? (int)$_GET['id'] : 0;
    $comment = $commentModel->getById($commentId);

    // Strictly allow only the author to delete their comment
    if ($comment && (int)$comment['user_id'] === $userId) {
        $commentModel->delete($commentId);
        setFlash('success', 'Your comment was deleted.');
    } else {
        setFlash('error', 'You are not authorized to delete this comment.');
    }
}

// Redirect back to recipe view
header('Location: recipe-view.php?id=' . $recipeId);
exit;
