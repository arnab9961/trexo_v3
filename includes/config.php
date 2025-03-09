<?php
// Database Configuration
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'tourism_db');

// Establish database connection
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set character set
mysqli_set_charset($conn, "utf8");

// Session start
session_start();

// Function to sanitize input data
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}

// Function to redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Function to check if user is admin
function is_admin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin';
}

// Function to display error message
function display_error($message) {
    return "<div class='alert alert-danger'>$message</div>";
}

// Function to display success message
function display_success($message) {
    return "<div class='alert alert-success'>$message</div>";
}
?> 