<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Redirect to a URL
 * 
 * @param string $url The URL to redirect to
 * @return void
 */
if (!function_exists('redirect')) {
    function redirect($url) {
        header("Location: $url");
        exit;
    }
}

/**
 * Check if user is logged in
 * 
 * @return bool True if user is logged in, false otherwise
 */
if (!function_exists('is_logged_in')) {
    function is_logged_in() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
}

/**
 * Check if user is an admin
 * 
 * @return bool True if user is an admin, false otherwise
 */
if (!function_exists('is_admin')) {
    function is_admin() {
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
    }
}

/**
 * Sanitize user input
 * 
 * @param string $data The data to sanitize
 * @return string The sanitized data
 */
if (!function_exists('sanitize_input')) {
    function sanitize_input($data) {
        global $conn;
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        if ($conn) {
            $data = mysqli_real_escape_string($conn, $data);
        }
        return $data;
    }
}

/**
 * Display success message
 * 
 * @param string $message The success message
 * @return string HTML for displaying the success message
 */
if (!function_exists('display_success')) {
    function display_success($message) {
        return '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>' . $message . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
    }
}

/**
 * Display error message
 * 
 * @param string $message The error message
 * @return string HTML for displaying the error message
 */
if (!function_exists('display_error')) {
    function display_error($message) {
        return '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>' . $message . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
    }
}

/**
 * Generate a unique filename
 * 
 * @param string $extension The file extension
 * @return string The unique filename
 */
if (!function_exists('generate_filename')) {
    function generate_filename($extension) {
        return uniqid() . '_' . time() . '.' . $extension;
    }
}

/**
 * Format price with currency symbol
 * 
 * @param float $price The price to format
 * @return string The formatted price
 */
if (!function_exists('format_price')) {
    function format_price($price) {
        return '৳' . number_format($price, 2);
    }
}
?> 