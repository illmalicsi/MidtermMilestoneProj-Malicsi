<?php
// register.php - Member account creation with input validation and password hashing

require_once 'db.php';
require_once 'includes/helpers.php';
require_once 'classes/Auth.php';
require_once 'classes/User.php';

Auth::start();

// Redirect if already logged in
if (Auth::check()) {
    header('Location: index.php');
    exit;
}

$userModel = new User($pdo);
$error = '';
$firstName = '';
$lastName = '';
$email = '';

// Handle registration POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validate inputs server-side
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } elseif ($userModel->findByEmail($email)) {
        $error = 'This email address is already registered. Please log in instead.';
    } else {
        // Register user and hash password with password_hash()
        $userId = $userModel->register($firstName, $lastName, $email, $password);
        if ($userId) {
            setFlash('success', 'Account created successfully! Please log in.');
            header('Location: login.php');
            exit;
        } else {
            $error = 'An error occurred while creating your account. Please try again.';
        }
    }
}

$pageTitle = 'Create Account';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $pageTitle; ?> | BaryoBites</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body style="background-color: var(--color-bg);">

<div class="auth-wrapper">
  <div class="auth-card">
    <div class="auth-header">
      <div style="display: flex; justify-content: center; margin-bottom: var(--space-3);">
        <svg style="width: 36px; height: 36px; color: var(--color-primary);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 2a5 5 0 0 1 5 5v3H7V7a5 5 0 0 1 5-5z"></path>
          <path d="M5 10h14v10a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V10z"></path>
          <line x1="12" y1="14" x2="12" y2="18"></line>
        </svg>
      </div>
      <h1>Join BaryoBites</h1>
      <p>Create a free member account to share recipes and save your favorites.</p>
    </div>

    <!-- Error alert -->
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

    <!-- Registration Form -->
    <form method="POST" action="register.php">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" for="reg-first-name">First Name *</label>
          <input type="text" id="reg-first-name" name="first_name" class="form-control" placeholder="e.g., Maria" value="<?php echo e($firstName); ?>" required autofocus>
        </div>
        <div class="form-group">
          <label class="form-label" for="reg-last-name">Last Name *</label>
          <input type="text" id="reg-last-name" name="last_name" class="form-control" placeholder="e.g., Santos" value="<?php echo e($lastName); ?>" required>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="reg-email">Email Address *</label>
        <input type="email" id="reg-email" name="email" class="form-control" placeholder="maria@example.com" value="<?php echo e($email); ?>" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="reg-password">Password *</label>
        <input type="password" id="reg-password" name="password" class="form-control" placeholder="At least 8 characters" minlength="8" required>
        <div class="form-hint">At least 8 characters long.</div>
      </div>

      <div class="form-group">
        <label class="form-label" for="reg-confirm-password">Confirm Password *</label>
        <input type="password" id="reg-confirm-password" name="confirm_password" class="form-control" placeholder="Re-enter password" minlength="8" required>
      </div>

      <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: var(--space-2);">Create Account</button>
    </form>

    <div class="auth-footer">
      Already have an account? <a href="login.php">Log in here</a>
    </div>
  </div>
</div>

</body>
</html>