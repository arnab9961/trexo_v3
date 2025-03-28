<?php
require_once '../includes/config.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    $_SESSION['error_message'] = 'You do not have permission to perform this action.';
    redirect('index.php');
}

// Check if booking ID is provided
if (!isset($_POST['booking_id']) || empty($_POST['booking_id'])) {
    $_SESSION['error_message'] = 'Invalid booking ID.';
    redirect('bookings.php');
}

$booking_id = (int)$_POST['booking_id'];

// First, check if the booking exists
$check_query = "SELECT id FROM bookings WHERE id = ?";
$check_stmt = mysqli_prepare($conn, $check_query);
mysqli_stmt_bind_param($check_stmt, "i", $booking_id);
mysqli_stmt_execute($check_stmt);
mysqli_stmt_store_result($check_stmt);

if (mysqli_stmt_num_rows($check_stmt) === 0) {
    $_SESSION['error_message'] = 'Booking not found.';
    redirect('bookings.php');
}

// Delete related payment records first (foreign key constraint)
$delete_payments = "DELETE FROM payments WHERE booking_id = ?";
$stmt_payments = mysqli_prepare($conn, $delete_payments);
mysqli_stmt_bind_param($stmt_payments, "i", $booking_id);
mysqli_stmt_execute($stmt_payments);

// Now delete the booking
$delete_query = "DELETE FROM bookings WHERE id = ?";
$stmt = mysqli_prepare($conn, $delete_query);
mysqli_stmt_bind_param($stmt, "i", $booking_id);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['success_message'] = 'Booking deleted successfully.';
} else {
    $_SESSION['error_message'] = 'Error deleting booking: ' . mysqli_error($conn);
}

redirect('bookings.php');
?> 