<?php
require_once 'includes/config.php';

// Store success message before destroying the session
$_SESSION['success_message'] = 'You have been successfully logged out.';

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Start a new session to store the success message
session_start();
$_SESSION['success_message'] = 'You have been successfully logged out.';

// Redirect to login page
header("Location: login.php");
exit();
?> 