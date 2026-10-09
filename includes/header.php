<?php
// includes/header.php - Shared responsive navigation and header

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentUser = $_SESSION['user'] ?? null;
$currentUri = basename($_SERVER['PHP_SELF'] ?? 'index.php');

// Build display name and avatar letter for logged-in user
$displayName = '';
$avatarLetter = 'U';
if (!empty($currentUser)) {
    $displayName = trim(($currentUser['first_name'] ?? '') . ' ' . ($currentUser['last_name'] ?? ''));
    if (empty($displayName)) {
        $displayName = $currentUser['email'] ?? 'Member';
    }
    $avatarLetter = strtoupper(substr($currentUser['first_name'] ?? ($currentUser['email'] ?? 'U'), 0, 1));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <!-- Ensure proper mobile and desktop responsive viewport scaling -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | BaryoBites' : 'BaryoBites - Filipino Home Cooking'; ?></title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- Main Top Navigation -->
<header class="navbar">
  <div class="container navbar-inner">
    <!-- Brand Logo -->
    <a href="index.php" class="brand">
      <svg class="brand-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 2a5 5 0 0 1 5 5v3H7V7a5 5 0 0 1 5-5z"></path>
        <path d="M5 10h14v10a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V10z"></path>
        <line x1="12" y1="14" x2="12" y2="18"></line>
      </svg>
      Baryo<span>Bites</span>
    </a>

    <!-- Navigation Links -->
    <nav>
      <ul class="nav-links">
        <li>
          <a href="index.php" class="nav-link <?php echo $currentUri === 'index.php' ? 'active' : ''; ?>">
            Recipes
          </a>
        </li>
        <li>
          <a href="favorites.php" class="nav-link <?php echo $currentUri === 'favorites.php' ? 'active' : ''; ?>">
            Favorites
          </a>
        </li>
        <li>
          <a href="recipe-create.php" class="btn btn-primary btn-sm">
            + Share Recipe
          </a>
        </li>
      </ul>
    </nav>

    <!-- User Profile / Auth State -->
    <div class="nav-user">
      <?php if (!empty($currentUser)): ?>
        <div class="user-badge">
          <span class="author-avatar"><?php echo htmlspecialchars($avatarLetter); ?></span>
          <strong><?php echo htmlspecialchars($displayName); ?></strong>
        </div>
        <a href="logout.php" class="btn btn-outline btn-sm">Log out</a>
      <?php else: ?>
        <a href="login.php" class="btn btn-outline btn-sm">Log in</a>
        <a href="register.php" class="btn btn-primary btn-sm">Register</a>
      <?php endif; ?>
    </div>
  </div>
</header>

<main class="main-content">
  <div class="container">
