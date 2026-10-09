<?php
// comment-edit.php - Edit comment with author ownership verification and edit indicator

require_once 'db.php';
require_once 'includes/helpers.php';
require_once 'classes/Auth.php';
require_once 'classes/Comment.php';

// Enforce member access
Auth::requireLogin();

$commentModel = new Comment($pdo);
$userId = Auth::id();

$commentId = !empty($_GET['id']) ? (int)$_GET['id'] : 0;
$comment = $commentModel->getById($commentId);

// Strictly allow only the author to edit
if (!$comment || (int)$comment['user_id'] !== $userId) {
    setFlash('error', 'You are not authorized to edit this comment.');
    header('Location: index.php');
    exit;
}

$error = '';
// Handle update POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = trim($_POST['body'] ?? '');
    if (!empty($body)) {
        // Update comment body (sets updated_at = NOW() for edit indicator)
        $commentModel->update($commentId, $body);
        setFlash('success', 'Comment updated successfully.');
        header('Location: recipe-view.php?id=' . $comment['recipe_id']);
        exit;
    } else {
        $error = 'Comment cannot be empty.';
    }
}

$pageTitle = 'Edit Comment';
require_once 'includes/header.php';
?>

<div class="container container-narrow">
  <div class="hero" style="margin-bottom: var(--space-6);">
    <h1 class="hero-title">Edit Your Comment</h1>
    <p class="hero-subtitle">Update your comment. Others will see an (edited) badge next to your timestamp.</p>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger">
      <span><?php echo e($error); ?></span>
    </div>
  <?php endif; ?>

  <!-- Comment Edit Form -->
  <form class="card-form" method="POST" action="comment-edit.php?id=<?php echo $comment['id']; ?>">
    <div class="form-group">
      <label class="form-label" for="comment-body">Your Comment *</label>
      <textarea id="comment-body" name="body" class="form-control" rows="4" required><?php echo e($_POST['body'] ?? $comment['body']); ?></textarea>
    </div>

    <div style="display: flex; justify-content: flex-end; gap: var(--space-3); margin-top: var(--space-6);">
      <a href="recipe-view.php?id=<?php echo $comment['recipe_id']; ?>" class="btn btn-outline">Cancel</a>
      <button type="submit" class="btn btn-primary">Save Changes</button>
    </div>
  </form>
</div>

<?php require_once 'includes/footer.php'; ?>
