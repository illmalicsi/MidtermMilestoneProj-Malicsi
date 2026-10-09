<?php
// recipe-view.php - Single recipe details, ingredients checklist, print feature, and comments thread

require_once 'db.php';
require_once 'includes/helpers.php';
require_once 'classes/Auth.php';
require_once 'classes/Recipe.php';
require_once 'classes/Ingredient.php';
require_once 'classes/Comment.php';
require_once 'classes/Favorite.php';

// Enforce member access
Auth::requireLogin();

// Instantiate models
$recipeModel = new Recipe($pdo);
$ingredientModel = new Ingredient($pdo);
$commentModel = new Comment($pdo);
$favoriteModel = new Favorite($pdo);

// Fetch recipe data
$recipeId = !empty($_GET['id']) ? (int)$_GET['id'] : 0;
$recipe = $recipeModel->getById($recipeId);

$currentUserId = Auth::id();
$isFavorite = false;
$ingredients = [];
$comments = [];
$stepLines = [];

if ($recipe) {
    // Check if current user saved this recipe
    $isFavorite = $favoriteModel->isFavorite($currentUserId, $recipeId);
    // Fetch recipe ingredients
    $ingredients = $ingredientModel->getByRecipeId($recipeId);
    // Fetch recipe comments
    $comments = $commentModel->getByRecipeId($recipeId);

    // Split cooking instructions by line break
    $rawSteps = explode("\n", str_replace("\r", "", $recipe['steps']));
    foreach ($rawSteps as $line) {
        $trimmed = trim($line);
        if ($trimmed !== '') {
            $stepLines[] = $trimmed;
        }
    }
}

// Retrieve flash messages
$flashSuccess = getFlash('success');
$flashError = getFlash('error');

$pageTitle = $recipe ? $recipe['title'] : 'Recipe Not Found';
require_once 'includes/header.php';
?>

<?php if (!$recipe): ?>
  <!-- Recipe Not Found Empty State -->
  <div class="empty-state">
    <h3>Recipe Not Found</h3>
    <p>The recipe you are looking for does not exist or may have been removed.</p>
    <a href="index.php" class="btn btn-primary btn-sm">Back to Recipes</a>
  </div>
<?php else: ?>
  <!-- Flash notifications -->
  <?php if (!empty($flashSuccess)): ?>
    <div class="alert alert-success">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="20 6 9 17 4 12"></polyline>
      </svg>
      <span><?php echo e($flashSuccess); ?></span>
    </div>
  <?php endif; ?>

  <?php if (!empty($flashError)): ?>
    <div class="alert alert-danger">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="12" y1="8" x2="12" y2="12"></line>
        <line x1="12" y1="16" x2="12.01" y2="16"></line>
      </svg>
      <span><?php echo e($flashError); ?></span>
    </div>
  <?php endif; ?>

  <!-- Recipe Header -->
  <div class="recipe-header">
    <div class="recipe-meta-top">
      <span class="card-badge"><?php echo e($recipe['category_name']); ?></span>
      <span class="comment-date">Published on <?php echo date('M j, Y', strtotime($recipe['created_at'])); ?></span>
      <!-- Edit Indicator for modified recipes -->
      <?php if (!empty($recipe['updated_at'])): ?>
        <span class="tag-edited">(Edited)</span>
      <?php endif; ?>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap;">
      <h1 class="recipe-title"><?php echo e($recipe['title']); ?></h1>
      <div style="display: flex; gap: 0.5rem; align-items: center;">
        <!-- Favorite Toggle Button (AJAX) -->
        <button type="button" class="btn btn-outline btn-favorite <?php echo $isFavorite ? 'active' : ''; ?>" data-recipe-id="<?php echo $recipe['id']; ?>" style="border: 1px solid var(--color-border); padding: 0.55rem 1rem;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="<?php echo $isFavorite ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
          </svg>
          <span>Favorite</span>
        </button>

        <!-- Custom Feature: Print-Friendly Recipe View -->
        <button type="button" class="btn btn-outline btn-sm" onclick="window.print()" title="Print Recipe Card">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="6 9 6 2 18 2 18 9"></polyline>
            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
            <rect x="6" y="14" width="12" height="8"></rect>
          </svg>
          <span>Print Recipe</span>
        </button>

        <!-- Edit/Delete actions strictly visible only to author -->
        <?php if ($currentUserId === (int)$recipe['user_id']): ?>
          <a href="recipe-edit.php?id=<?php echo $recipe['id']; ?>" class="btn btn-outline btn-sm">Edit</a>
          <a href="recipe-delete.php?id=<?php echo $recipe['id']; ?>" class="btn btn-danger-outline btn-sm" onclick="return confirm('Are you sure you want to delete this recipe?')">Delete</a>
        <?php endif; ?>
      </div>
    </div>

    <p class="recipe-description"><?php echo e($recipe['description']); ?></p>

    <!-- Highlight Meta Box -->
    <div class="recipe-highlight-box">
      <div class="highlight-item">
        <span class="highlight-label">Cooked By</span>
        <span class="highlight-value"><?php echo e(trim(($recipe['first_name'] ?? '') . ' ' . ($recipe['last_name'] ?? '')) ?: 'Home Cook'); ?></span>
      </div>
      <div class="highlight-item">
        <span class="highlight-label">Category</span>
        <span class="highlight-value"><?php echo e($recipe['category_name']); ?></span>
      </div>
      <div class="highlight-item">
        <span class="highlight-label">Status</span>
        <span class="highlight-value"><?php echo !empty($recipe['updated_at']) ? 'Updated' : 'Original'; ?></span>
      </div>
    </div>
  </div>

  <div class="recipe-detail-grid">
    <!-- Left Column: Ingredients Checklist -->
    <aside class="section-panel">
      <h2 class="section-panel-title">
        Ingredients
        <span style="font-size: var(--text-xs); color: var(--color-text-muted); font-weight: normal;"><?php echo count($ingredients); ?> items</span>
      </h2>
      <?php if (empty($ingredients)): ?>
        <p style="font-size: var(--text-sm); color: var(--color-text-muted);">No ingredients listed.</p>
      <?php else: ?>
        <ul class="ingredients-list">
          <?php foreach ($ingredients as $ing): ?>
            <li class="ingredient-item">
              <span class="ingredient-bullet"></span>
              <span><?php echo e($ing['name']); ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </aside>

    <!-- Right Column: Numbered Cooking Instructions -->
    <section class="section-panel">
      <h2 class="section-panel-title">Cooking Instructions</h2>
      <?php if (empty($stepLines)): ?>
        <p style="font-size: var(--text-sm); color: var(--color-text-muted);">No cooking instructions provided.</p>
      <?php else: ?>
        <div class="steps-list">
          <?php foreach ($stepLines as $index => $step): ?>
            <div class="step-item">
              <div class="step-number"><?php echo $index + 1; ?></div>
              <div class="step-text"><?php echo e($step); ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  </div>

  <!-- Comments Section -->
  <section class="comments-section">
    <h2 style="font-size: var(--text-xl); font-weight: 700; margin-bottom: var(--space-4);">
      Comments & Feedback (<?php echo count($comments); ?>)
    </h2>

    <!-- Form to post a new comment -->
    <form class="card-form" method="POST" action="comment-action.php" style="padding: var(--space-5); margin-bottom: var(--space-6);">
      <input type="hidden" name="recipe_id" value="<?php echo $recipe['id']; ?>">
      <input type="hidden" name="action" value="create">
      <div class="form-group" style="margin-bottom: var(--space-3);">
        <label class="form-label" for="comment-text">Leave a comment or your personal cooking tip:</label>
        <textarea id="comment-text" name="body" class="form-control" placeholder="Write your feedback or suggestions here..." rows="3" required></textarea>
      </div>
      <div style="display: flex; justify-content: flex-end;">
        <button type="submit" class="btn btn-primary btn-sm">Post Comment</button>
      </div>
    </form>

    <!-- Comments List -->
    <div class="comment-list">
      <?php if (empty($comments)): ?>
        <p style="font-size: var(--text-sm); color: var(--color-text-muted); text-align: center; padding: var(--space-4) 0;">No comments yet. Be the first to share your feedback!</p>
      <?php else: ?>
        <?php foreach ($comments as $c): ?>
          <div class="comment-card" id="comment-<?php echo $c['id']; ?>">
            <div class="comment-header">
              <div class="comment-author-info">
                <span class="author-avatar"><?php echo strtoupper(substr($c['first_name'] ?? 'U', 0, 1)); ?></span>
                <span class="comment-author-name"><?php echo e(trim(($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? '')) ?: 'Home Cook'); ?></span>
                <span class="comment-date">&bull; <?php echo date('M j, Y g:i a', strtotime($c['created_at'])); ?></span>
                <!-- Comment edit indicator -->
                <?php if (!empty($c['updated_at'])): ?>
                  <span class="tag-edited">(edited)</span>
                <?php endif; ?>
              </div>

              <!-- Only comment author can edit or delete -->
              <?php if ($currentUserId === (int)$c['user_id']): ?>
                <div style="display: flex; gap: var(--space-2); font-size: var(--text-xs);">
                  <a href="comment-edit.php?id=<?php echo $c['id']; ?>" style="color: var(--color-primary); font-weight: 500;">Edit</a>
                  <span style="color: var(--color-border);">&bull;</span>
                  <a href="comment-action.php?action=delete&id=<?php echo $c['id']; ?>&recipe_id=<?php echo $recipe['id']; ?>" style="color: var(--color-danger); font-weight: 500;" onclick="return confirm('Delete this comment?')">Delete</a>
                </div>
              <?php endif; ?>
            </div>
            <p class="comment-body"><?php echo e($c['body']); ?></p>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
