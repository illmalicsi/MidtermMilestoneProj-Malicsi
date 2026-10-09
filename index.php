<?php
// index.php - Community recipe feed with keyword search, category filter, and latest-first ordering

require_once 'db.php';
require_once 'includes/helpers.php';
require_once 'classes/Auth.php';
require_once 'classes/Category.php';
require_once 'classes/Recipe.php';
require_once 'classes/Favorite.php';

// Enforce visitor protection: guests are redirected to login.php
Auth::requireLogin();

// Instantiate models
$categoryModel = new Category($pdo);
$recipeModel = new Recipe($pdo);
$favoriteModel = new Favorite($pdo);

$currentUserId = Auth::id();

// Read query parameters
$categories = $categoryModel->getAll();
$searchQuery = trim($_GET['q'] ?? '');
$selectedCategory = !empty($_GET['category']) ? (int)$_GET['category'] : null;

// Fetch recipes ordered by latest first, filtered by search query and category
$recipes = $recipeModel->getAll($searchQuery, $selectedCategory);

// Retrieve session flash notifications
$flashSuccess = getFlash('success');
$flashError = getFlash('error');
$flashInfo = getFlash('info');

$pageTitle = 'Home Recipes';
require_once 'includes/header.php';
?>

<!-- Flash message alerts -->
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

<?php if (!empty($flashInfo)): ?>
  <div class="alert alert-info">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="12" r="10"></circle>
      <line x1="12" y1="16" x2="12" y2="12"></line>
      <line x1="12" y1="8" x2="12.01" y2="8"></line>
    </svg>
    <span><?php echo e($flashInfo); ?></span>
  </div>
<?php endif; ?>

<!-- Intro Section -->
<div class="hero">
  <h1 class="hero-title">Community Kitchen</h1>
  <p class="hero-subtitle">Share, discover, and cook comforting Filipino home recipes with your neighbors.</p>
</div>

<!-- Search Form and Category Filter Buttons -->
<div class="filter-bar">
  <form class="search-form" method="GET" action="index.php">
    <?php if ($selectedCategory): ?>
      <input type="hidden" name="category" value="<?php echo e($selectedCategory); ?>">
    <?php endif; ?>
    <div class="search-input-wrap">
      <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="11" cy="11" r="8"></circle>
        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
      </svg>
      <input type="text" name="q" class="search-input" placeholder="Search recipes (e.g., Sinigang, Adobo, Ginataan)..." value="<?php echo e($searchQuery); ?>">
    </div>
    <button type="submit" class="btn btn-primary">Search</button>
    <?php if (!empty($searchQuery) || !empty($selectedCategory)): ?>
      <a href="index.php" class="btn btn-outline">Clear</a>
    <?php endif; ?>
  </form>

  <!-- Category Pills Filter -->
  <div class="category-pills">
    <a href="index.php<?php echo !empty($searchQuery) ? '?q=' . urlencode($searchQuery) : ''; ?>" class="pill <?php echo empty($selectedCategory) ? 'active' : ''; ?>">
      All
    </a>
    <?php foreach ($categories as $cat): ?>
      <?php 
        $catUrl = 'index.php?category=' . $cat['id'];
        if (!empty($searchQuery)) {
            $catUrl .= '&q=' . urlencode($searchQuery);
        }
      ?>
      <a href="<?php echo $catUrl; ?>" class="pill <?php echo $selectedCategory === (int)$cat['id'] ? 'active' : ''; ?>">
        <?php echo e($cat['name']); ?>
      </a>
    <?php endforeach; ?>
  </div>
</div>

<!-- Recipe Grid -->
<?php if (empty($recipes)): ?>
  <!-- Empty State when no recipes match search or category -->
  <div class="empty-state">
    <h3>No Recipes Found</h3>
    <p>There are no recipes matching your criteria yet. Be the first to share one!</p>
    <a href="recipe-create.php" class="btn btn-primary btn-sm">+ Share a Recipe</a>
  </div>
<?php else: ?>
  <!-- Recipe Cards Feed (Latest First) -->
  <div class="recipe-grid">
    <?php foreach ($recipes as $r): ?>
      <?php $isFav = $favoriteModel->isFavorite($currentUserId, (int)$r['id']); ?>
      <article class="recipe-card">
        <div class="card-top">
          <span class="card-badge"><?php echo e($r['category_name']); ?></span>
          <!-- Zero-reload favorite toggle button -->
          <button type="button" class="btn-favorite <?php echo $isFav ? 'active' : ''; ?>" data-recipe-id="<?php echo $r['id']; ?>" title="<?php echo $isFav ? 'Remove from Favorites' : 'Save to Favorites'; ?>">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="<?php echo $isFav ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
          <!-- Author display and edit status -->
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
