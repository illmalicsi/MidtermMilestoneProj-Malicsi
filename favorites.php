<?php
// favorites.php - Member's personal collection of saved favorite recipes

require_once 'db.php';
require_once 'includes/helpers.php';
require_once 'classes/Auth.php';
require_once 'classes/Favorite.php';

// Enforce member access
Auth::requireLogin();

// Fetch all recipes favorited by current member
$favoriteModel = new Favorite($pdo);
$favoriteRecipes = $favoriteModel->getUserFavorites(Auth::id());

$pageTitle = 'My Saved Favorites';
require_once 'includes/header.php';
?>

<!-- Intro Section -->
<div class="hero">
  <h1 class="hero-title">Saved Favorites</h1>
  <p class="hero-subtitle">Your personal collection of recipes you'd love to cook again.</p>
</div>

<!-- Favorites Grid -->
<?php if (empty($favoriteRecipes)): ?>
  <!-- Empty State when member has not favorited any recipe -->
  <div class="empty-state">
    <h3>No Favorites Saved Yet</h3>
    <p>You haven't saved any recipes to your personal collection yet.</p>
    <a href="index.php" class="btn btn-primary btn-sm">Browse Community Recipes</a>
  </div>
<?php else: ?>
  <!-- Saved Recipe Cards -->
  <div class="recipe-grid">
    <?php foreach ($favoriteRecipes as $r): ?>
      <article class="recipe-card">
        <div class="card-top">
          <span class="card-badge"><?php echo e($r['category_name']); ?></span>
          <!-- Zero-reload favorite toggle button -->
          <button type="button" class="btn-favorite active" data-recipe-id="<?php echo $r['id']; ?>" title="Remove from Favorites">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
            </svg>
          </button>
        </div>

        <div class="card-body">
          <h2 class="card-title">
            <a href="recipe-view.php?id=<?php echo $r['id']; ?>">
              <?php echo e($r['title']); ?>
            </a>
          </h2>
          <p class="card-desc"><?php echo e($r['description']); ?></p>
        </div>

        <div class="card-footer">
          <div class="card-author">
            <span class="author-avatar"><?php echo strtoupper(substr($r['first_name'] ?? 'U', 0, 1)); ?></span>
            <span><?php echo e(trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')) ?: 'Home Cook'); ?></span>
            <?php if (!empty($r['updated_at'])): ?>
              <span class="tag-edited">(Edited)</span>
            <?php endif; ?>
          </div>
          <span><?php echo date('M j, Y', strtotime($r['created_at'])); ?></span>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
