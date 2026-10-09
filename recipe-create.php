<?php
// recipe-create.php - Form and handler for sharing a new recipe with dynamic ingredients

require_once 'db.php';
require_once 'includes/helpers.php';
require_once 'classes/Auth.php';
require_once 'classes/Category.php';
require_once 'classes/Recipe.php';
require_once 'classes/Ingredient.php';

// Enforce member-only access
Auth::requireLogin();

// Instantiate models
$categoryModel = new Category($pdo);
$recipeModel = new Recipe($pdo);
$ingredientModel = new Ingredient($pdo);

$categories = $categoryModel->getAll();
$error = '';

$title = '';
$categoryId = '';
$description = '';
$steps = '';
$cleanIngredients = [];

// Handle recipe submission POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $description = trim($_POST['description'] ?? '');
    $steps = trim($_POST['steps'] ?? '');
    $rawIngredients = $_POST['ingredients'] ?? [];

    // Filter out blank ingredient inputs
    foreach ($rawIngredients as $item) {
        $trimmed = trim($item);
        if ($trimmed !== '') {
            $cleanIngredients[] = $trimmed;
        }
    }

    // Validate form inputs
    if (empty($title) || empty($categoryId) || empty($description) || empty($steps)) {
        $error = 'Please fill out all required fields.';
    } elseif (empty($cleanIngredients)) {
        $error = 'Please add at least one ingredient.';
    } else {
        $userId = Auth::id();
        // Insert recipe into database
        $recipeId = $recipeModel->create($userId, $categoryId, $title, $description, $steps);

        if ($recipeId) {
            // Insert each ingredient row
            foreach ($cleanIngredients as $item) {
                $ingredientModel->add($recipeId, $item);
            }
            setFlash('success', 'Your recipe has been shared successfully!');
            header('Location: recipe-view.php?id=' . $recipeId);
            exit;
        } else {
            $error = 'Failed to save the recipe. Please try again.';
        }
    }
}

$pageTitle = 'Share a Recipe';
require_once 'includes/header.php';
?>

<div class="container container-narrow">
  <!-- Page Heading -->
  <div class="hero" style="margin-bottom: var(--space-6);">
    <h1 class="hero-title">Share a Recipe</h1>
    <p class="hero-subtitle">Share your family's favorite Filipino dishes and secret ingredients with your neighbors.</p>
  </div>

  <!-- Error Notification -->
  <?php if (!empty($error)): ?>
    <div class="alert alert-danger">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="12" y1="8" x2="12" y2="12"></line>
        <line x1="12" y1="16" x2="12.01" y2="16"></line>
      </svg>
      <span><?php echo e($error); ?></span>
    </div>
  <?php endif; ?>

  <!-- Recipe Form -->
  <form class="card-form" method="POST" action="recipe-create.php">
    <!-- Title -->
    <div class="form-group">
      <label class="form-label" for="title">Recipe Title *</label>
      <input type="text" id="title" name="title" class="form-control" placeholder="e.g., Sinigang na Baboy sa Sampalok" value="<?php echo e($title); ?>" required>
    </div>

    <!-- Category Dropdown populated from categories table -->
    <div class="form-group">
      <label class="form-label" for="category_id">Category *</label>
      <select id="category_id" name="category_id" class="form-control" required>
        <option value="" disabled <?php echo empty($categoryId) ? 'selected' : ''; ?>>Select a category...</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?php echo $cat['id']; ?>" <?php echo $categoryId === (int)$cat['id'] ? 'selected' : ''; ?>>
            <?php echo e($cat['name']); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Description -->
    <div class="form-group">
      <label class="form-label" for="description">Short Description *</label>
      <textarea id="description" name="description" class="form-control" placeholder="Share why this dish is special or what makes it delicious..." rows="3" required><?php echo e($description); ?></textarea>
      <div class="form-hint">A brief summary introducing your recipe before the ingredients.</div>
    </div>

    <!-- Dynamic Ingredients: JS allows adding and removing rows -->
    <div class="form-group" style="margin-top: var(--space-4);">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-2);">
        <label class="form-label" style="margin-bottom: 0;">Ingredients *</label>
        <button type="button" id="btn-add-ingredient" class="btn btn-soft btn-sm">
          + Add Ingredient
        </button>
      </div>
      <div class="form-hint" style="margin-bottom: var(--space-3);">Include the amount and the ingredient name.</div>

      <!-- Ingredient rows container -->
      <div id="ingredients-container">
        <?php if (!empty($cleanIngredients)): ?>
          <?php foreach ($cleanIngredients as $index => $item): ?>
            <div class="ingredient-row">
              <input type="text" name="ingredients[]" class="form-control" placeholder="e.g., 500g Pork belly (liempo)" value="<?php echo e($item); ?>" <?php echo $index === 0 ? 'required' : ''; ?>>
              <button type="button" class="btn-remove-row" title="Remove ingredient" aria-label="Remove">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="18" y1="6" x2="6" y2="18"></line>
                  <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
              </button>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <!-- Starter ingredient rows (Cooks can fill as many as needed) -->
          <div class="ingredient-row">
            <input type="text" name="ingredients[]" class="form-control" placeholder="e.g., 500g Pork belly (liempo), sliced" required>
            <button type="button" class="btn-remove-row" title="Remove ingredient" aria-label="Remove">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </button>
          </div>
          <div class="ingredient-row">
            <input type="text" name="ingredients[]" class="form-control" placeholder="e.g., 1 bunch Fresh water spinach (kangkong)">
            <button type="button" class="btn-remove-row" title="Remove ingredient" aria-label="Remove">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </button>
          </div>
          <div class="ingredient-row">
            <input type="text" name="ingredients[]" class="form-control" placeholder="e.g., 2 tbsp Fish sauce (patis) or to taste">
            <button type="button" class="btn-remove-row" title="Remove ingredient" aria-label="Remove">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </button>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Cooking Steps -->
    <div class="form-group" style="margin-top: var(--space-6);">
      <label class="form-label" for="steps">Cooking Instructions *</label>
      <textarea id="steps" name="steps" class="form-control" rows="6" placeholder="List step-by-step instructions. Separate each step on a new line or with numbers..." required><?php echo e($steps); ?></textarea>
      <div class="form-hint">Clear, numbered steps help fellow home cooks follow along easily.</div>
    </div>

    <!-- Form Actions -->
    <div style="display: flex; justify-content: flex-end; gap: var(--space-3); margin-top: var(--space-8); padding-top: var(--space-4); border-top: 1px solid var(--color-border);">
      <a href="index.php" class="btn btn-outline">Cancel</a>
      <button type="submit" class="btn btn-primary">Publish Recipe</button>
    </div>
  </form>
</div>

<?php require_once 'includes/footer.php'; ?>
