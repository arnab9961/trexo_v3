<?php
require_once 'includes/header.php';

// Check if user is logged in
if (!is_logged_in()) {
    $_SESSION['error_message'] = 'Please login to view your bookings.';
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Process booking cancellation
if (isset($_GET['cancel']) && !empty($_GET['cancel'])) {
    $booking_id = (int)$_GET['cancel'];
    
    // Check if booking belongs to user
    $check_query = "SELECT * FROM bookings WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "ii", $booking_id, $user_id);
    mysqli_stmt_execute($stmt);
    $check_result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($check_result) == 1) {
        $booking = mysqli_fetch_assoc($check_result);
        
        // Only allow cancellation if status is pending
        if ($booking['status'] == 'pending') {
            $cancel_query = "UPDATE bookings SET status = 'cancelled' WHERE id = ?";
            $stmt = mysqli_prepare($conn, $cancel_query);
            mysqli_stmt_bind_param($stmt, "i", $booking_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = 'Booking cancelled successfully.';
            } else {
                $_SESSION['error_message'] = 'Failed to cancel booking. Please try again.';
            }
        } else {
            $_SESSION['error_message'] = 'Cannot cancel a booking that is already ' . $booking['status'] . '.';
        }
    } else {
        $_SESSION['error_message'] = 'Invalid booking ID.';
    }
    
    redirect('my_bookings.php');
}

// Get user's bookings
$bookings_query = "SELECT b.*, 
                  d.name as destination_name, d.image as destination_image,
                  p.name as package_name, p.image as package_image
                  FROM bookings b
                  LEFT JOIN destinations d ON b.destination_id = d.id
                  LEFT JOIN packages p ON b.package_id = p.id
                  WHERE b.user_id = ?
                  ORDER BY b.booking_date DESC";
$stmt = mysqli_prepare($conn, $bookings_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$bookings_result = mysqli_stmt_get_result($stmt);
?>

<div class="container">
    <h2 class="mb-4">My Bookings</h2>
    
    <?php if (mysqli_num_rows($bookings_result) > 0): ?>
        <div class="row">
            <?php while ($booking = mysqli_fetch_assoc($bookings_result)): ?>
                <div class="col-md-6 mb-4">
                    <div class="card booking-details">
                        <div class="card-body">
                            <h5 class="card-title">
                                <?php if ($booking['destination_id']): ?>
                                    <?php echo $booking['destination_name']; ?> (Destination)
                                <?php else: ?>
                                    <?php echo $booking['package_name']; ?> (Package)
                                <?php endif; ?>
                            </h5>
                            
                            <div class="booking-meta">
                                <small class="text-muted">Booked on: <?php echo date('F j, Y', strtotime($booking['booking_date'])); ?></small>
                                
                                <div>
                                    <span class="booking-status status-<?php echo $booking['status']; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <p class="card-text">
                                <strong>Travel Date:</strong> <?php echo date('F j, Y', strtotime($booking['travel_date'])); ?><br>
                                <strong>Travelers:</strong> <?php echo $booking['num_travelers']; ?><br>
                                <strong>Total Price:</strong> ৳<?php echo number_format($booking['total_price']); ?><br>
                                <strong>Payment:</strong> 
                                <span class="booking-status <?php echo ($booking['payment_status'] == 'completed') ? 'status-confirmed' : 'status-pending'; ?>">
                                    <?php echo ucfirst($booking['payment_status']); ?>
                                </span>
                            </p>
                            
                            <div class="mt-3">
                                <?php if ($booking['status'] == 'pending'): ?>
                                    <a href="my_bookings.php?cancel=<?php echo $booking['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this booking?')">Cancel Booking</a>
                                <?php endif; ?>
                                
                                <?php if ($booking['destination_id']): ?>
                                    <a href="destination_details.php?id=<?php echo $booking['destination_id']; ?>" class="btn btn-primary">View Destination</a>
                                <?php else: ?>
                                    <a href="package_details.php?id=<?php echo $booking['package_id']; ?>" class="btn btn-primary">View Package</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <p>You don't have any bookings yet. <a href="destinations.php">Explore destinations</a> or <a href="packages.php">view packages</a> to book your next trip!</p>
        </div>
    <?php endif; ?>
</div>

<?php
require_once 'includes/footer.php';
?> 