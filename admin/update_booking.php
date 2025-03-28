<?php
require_once '../includes/config.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    $_SESSION['error_message'] = 'You do not have permission to access the admin area.';
    redirect('../login.php');
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
    $status = isset($_POST['status']) ? sanitize_input($_POST['status']) : '';
    $payment_status = isset($_POST['payment_status']) ? sanitize_input($_POST['payment_status']) : '';
    
    if ($booking_id > 0) {
        // Update booking status
        $update_query = "UPDATE bookings SET status = ?, payment_status = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, "ssi", $status, $payment_status, $booking_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = "Booking #$booking_id has been updated successfully.";
        } else {
            $_SESSION['error_message'] = "Failed to update booking: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error_message'] = "Invalid booking ID.";
    }
    
    // Redirect back to my_bookings.php
    redirect('../my_bookings.php');
}
else {
    // If not a POST request, redirect
    redirect('../my_bookings.php');
}
?> 