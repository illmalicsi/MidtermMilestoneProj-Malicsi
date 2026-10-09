<?php
// logout.php - Destroy member session and redirect to login

require_once 'classes/Auth.php';

// Log out user and clear session cookies
Auth::logout();

// Redirect to login page
header('Location: login.php');
exit;
