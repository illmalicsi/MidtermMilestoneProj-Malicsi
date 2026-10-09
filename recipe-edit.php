<?php
// recipe-edit.php - Edit existing recipe with author validation and ingredient re-synchronization

require_once 'db.php';
require_once 'includes/helpers.php';
require_once 'classes/Auth.php';
require_once 'classes/Category.php';
require_once 'classes/Recipe.php';
require_once 'classes/Ingredient.php';

// Enforce member access
Auth::requireLogin();

// Instantiate models
$categoryModel = new Category($pdo);
$recipeModel = new Recipe($pdo);
$ingredientModel = new Ingredient($pdo);

$recipeId = !empty($_GET['id']) ? (int)$_GET['id'] : 0;
$recipe = $recipeModel->getById($recipeId);

// Strictly allow only the author to edit this recipe
if (!$recipe || (int)$recipe['user_id'] !== Auth::id()) {
    setFlash('error', 'You are not authorized to edit this recipe.');
    header('Location: index.php');
    exit;
}

$categories = $categoryModel->getAll();
$ingredients = $ingredientModel->getByRecipeId($recipeId);
$error = '';

// Handle update POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $description = trim($_POST['description'] ?? '');
    $steps = trim($_POST['steps'] ?? '');
    $rawIngredients = $_POST['ingredients'] ?? [];

    // Filter out blank inputs
    $cleanIngredients = [];
    foreach ($rawIngredients as $item) {
        $trimmed = trim($item);
        if ($trimmed !== '') {
            $cleanIngredients[] = $trimmed;
        }
    }

    // Validate inputs
    if (empty($title) || empty($categoryId) || empty($description) || empty($steps)) {
        $error = 'Please fill out all required fields.';
    } elseif (empty($cleanIngredients)) {
        $error = 'Please include at least one ingredient.';
    } else {
        // Update recipe record (sets updated_at = NOW() for edit indicator)
        $success = $recipeModel->update($recipeId, $categoryId, $title, $description, $steps);

        if ($success) {
            // Re-sync ingredients list
            $ingredientModel->deleteByRecipeId($recipeId);
            foreach ($cleanIngredients as $item) {
                $ingredientModel->add($recipeId, $item);
            }

            setFlash('success', 'Recipe updated successfully!');
            header('Location: recipe-view.php?id=' . $recipeId);
            exit;
        } else {
            $error = 'Failed to update the recipe. Please try again.';
        }
    }
}

$pageTitle = 'Edit Recipe';
require_once 'includes/header.php';
?>

<div class="container container-narrow">
  <div class="hero" style="margin-bottom: var(--space-6);">
    <h1 class="hero-title">Edit Recipe</h1>
    <p class="hero-subtitle">Update your recipe details or ingredients. Others will see an (Edited) badge on updated recipes.</p>
  </div>

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

  <!-- Recipe Edit Form -->
  <form class="card-form" method="POST" action="recipe-edit.php?id=<?php echo $recipe['id']; ?>">
    <!-- Title -->
    <div class="form-group">
      <label class="form-label" for="title">Recipe Title *</label>
      <input type="text" id="title" name="title" class="form-control" value="<?php echo e($_POST['title'] ?? $recipe['title']); ?>" required>
    </div>

    <!-- Category -->
    <div class="form-group">
      <label class="form-label" for="category_id">Category *</label>
      <select id="category_id" name="category_id" class="form-control" required>
        <?php foreach ($categories as $cat): ?>
          <option value="<?php echo $cat['id']; ?>" <?php echo ((int)($_POST['category_id'] ?? $recipe['category_id']) === (int)$cat['id']) ? 'selected' : ''; ?>>
            <?php echo e($cat['name']); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Description -->
    <div class="form-group">
      <label class="form-label" for="description">Short Description *</label>
      <textarea id="description" name="description" class="form-control" rows="3" required><?php echo e($_POST['description'] ?? $recipe['description']); ?></textarea>
    </div>

    <!-- Dynamic Ingredients -->
    <div class="form-group" style="margin-top: var(--space-4);">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-2);">
        <label class="form-label" style="margin-bottom: 0;">Ingredients *</label>
        <button type="button" id="btn-add-ingredient" class="btn btn-soft btn-sm">
          + Add Ingredient
        </button>
      </div>

      <!-- Ingredient inputs -->
      <?php
        $displayIngredients = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ingredients'])) {
            foreach ($_POST['ingredients'] as $raw) {
                $displayIngredients[] = trim($raw);
            }
        } else {
            foreach ($ingredients as $ing) {
                $displayIngredients[] = $ing['name'];
            }
        }
        if (empty($displayIngredients)) {
            $displayIngredients = [''];
        }
      ?>
      <div id="ingredients-container">
        <?php foreach ($displayIngredients as $index => $ingName): ?>
          <div class="ingredient-row">
            <input type="text" name="ingredients[]" class="form-control" placeholder="e.g., 500g Pork belly, sliced" value="<?php echo e($ingName); ?>" <?php echo $index === 0 ? 'required' : ''; ?>>
            <button type="button" class="btn-remove-row" title="Remove ingredient" aria-label="Remove">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
            </button>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Cooking Steps -->
    <div class="form-group" style="margin-top: var(--space-6);">
      <label class="form-label" for="steps">Cooking Instructions *</label>
      <textarea id="steps" name="steps" class="form-control" rows="6" required><?php echo e($_POST['steps'] ?? $recipe['steps']); ?></textarea>
    </div>

    <!-- Actions -->
    <div style="display: flex; justify-content: flex-end; gap: var(--space-3); margin-top: var(--space-8); padding-top: var(--space-4); border-top: 1px solid var(--color-border);">
      <a href="recipe-view.php?id=<?php echo $recipe['id']; ?>" class="btn btn-outline">Cancel</a>
      <button type="submit" class="btn btn-primary">Save Changes</button>
    </div>
  </form>
</div>

<?php require_once 'includes/footer.php'; ?>
