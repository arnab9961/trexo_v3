<?php
require_once '../includes/config.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    $_SESSION['error_message'] = 'You do not have permission to perform this action.';
    redirect('index.php');
}

// Check if booking ID and new status are provided
if (!isset($_POST['booking_id']) || !isset($_POST['new_status'])) {
    $_SESSION['error_message'] = 'Invalid request parameters.';
    redirect('bookings.php');
}

$booking_id = (int)$_POST['booking_id'];
$new_status = sanitize_input($_POST['new_status']);

// Validate status
$valid_statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
if (!in_array($new_status, $valid_statuses)) {
    $_SESSION['error_message'] = 'Invalid status value.';
    redirect('bookings.php');
}

// Update the booking status
$query = "UPDATE bookings SET status = ? WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "si", $new_status, $booking_id);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['success_message'] = 'Booking status updated successfully.';
} else {
    $_SESSION['error_message'] = 'Error updating booking status: ' . mysqli_error($conn);
}

redirect('bookings.php');
?> 