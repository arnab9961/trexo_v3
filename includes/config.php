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
if (!function_exists('sanitize_input')) {
    function sanitize_input($data) {
        global $conn;
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        $data = mysqli_real_escape_string($conn, $data);
        return $data;
    }
}

// Function to redirect
if (!function_exists('redirect')) {
    function redirect($url) {
        // Check if headers have already been sent
        if (!headers_sent()) {
            header("Location: $url");
            exit();
        } else {
            echo '<script>window.location.href="' . $url . '";</script>';
            echo '<noscript><meta http-equiv="refresh" content="0;url=' . $url . '"></noscript>';
            exit();
        }
    }
}

// Function to check if user is logged in
if (!function_exists('is_logged_in')) {
    function is_logged_in() {
        return isset($_SESSION['user_id']);
    }
}

// Function to check if user is admin
if (!function_exists('is_admin')) {
    function is_admin() {
        return isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin';
    }
}

// Function to display error message
if (!function_exists('display_error')) {
    function display_error($message) {
        return "<div class='alert alert-danger'>$message</div>";
    }
}

// Function to display success message
if (!function_exists('display_success')) {
    function display_success($message) {
        return "<div class='alert alert-success'>$message</div>";
    }
}
?> 