<?php
// login.php - Member login and session initiation

require_once 'db.php';
require_once 'includes/helpers.php';
require_once 'classes/Auth.php';
require_once 'classes/User.php';

Auth::start();

// Redirect to dashboard if user is already logged in
if (Auth::check()) {
    header('Location: index.php');
    exit;
}

$userModel = new User($pdo);
$error = '';
$email = '';

// Retrieve session flash notifications
$flashSuccess = getFlash('success');
$flashInfo = getFlash('info');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both your email address and password.';
    } else {
        // Authenticate against hashed password in database
        $user = $userModel->authenticate($email, $password);
        if ($user) {
            Auth::login($user);
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid email address or password.';
        }
    }
}

$pageTitle = 'Log In';
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
      <h1>Welcome Back</h1>
      <p>Log in to discover and share recipes with your community.</p>
    </div>

    <!-- Flash message display -->
    <?php if (!empty($flashSuccess)): ?>
      <div class="alert alert-success">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
        <span><?php echo e($flashSuccess); ?></span>
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

    <!-- Login form -->
    <form method="POST" action="login.php">
      <div class="form-group">
        <label class="form-label" for="login-email">Email Address</label>
        <input type="email" id="login-email" name="email" class="form-control" placeholder="e.g., name@example.com" value="<?php echo e($email); ?>" required autofocus>
      </div>

      <div class="form-group">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-2);">
          <label class="form-label" for="login-password" style="margin-bottom: 0;">Password</label>
        </div>
        <input type="password" id="login-password" name="password" class="form-control" placeholder="••••••••" required>
      </div>

      <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: var(--space-2);">Log In</button>
    </form>

    <div class="auth-footer">
      Don't have an account yet? <a href="register.php">Register here</a>
    </div>
  </div>
</div>

</body>
</html>