<?php
require_once 'includes/header.php';
require_once 'includes/navbar.php';
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

<div class="container py-5">
    
    
    <?php if (mysqli_num_rows($bookings_result) > 0): ?>
        <div class="row">
            <?php while ($booking = mysqli_fetch_assoc($bookings_result)): ?>
                <div class="col-lg-6 col-md-12 mb-4">
                    <div class="card booking-details">
                        <?php if ($booking['destination_id'] && $booking['destination_image']): ?>
                            <div class="booking-image-container">
                                <img src="images/destinations/<?php echo $booking['destination_image']; ?>" class="booking-image" alt="<?php echo $booking['destination_name']; ?>">
                            </div>
                        <?php elseif ($booking['package_id'] && $booking['package_image'] && $booking['package_name'] !== 'Family Adventure'): ?>
                            <div class="booking-image-container">
                                <img src="images/packages/<?php echo $booking['package_image']; ?>" class="booking-image" alt="<?php echo $booking['package_name']; ?>">
                            </div>
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">
                                <?php if ($booking['destination_id']): ?>
                                    <i class="fas fa-map-marker-alt me-2"></i><?php echo $booking['destination_name']; ?> (Destination)
                                <?php else: ?>
                                    <i class="fas fa-suitcase me-2"></i><?php echo $booking['package_name']; ?> (Package)
                                <?php endif; ?>
                            </h5>
                            
                            <div class="booking-meta">
                                <div>
                                    <small class="text-muted"><i class="far fa-calendar-alt me-1"></i> Booked on: <?php echo date('F j, Y', strtotime($booking['booking_date'])); ?></small>
                                </div>
                                
                                <div>
                                    <span class="booking-status status-<?php echo $booking['status']; ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="booking-info mb-3">
                                <p class="card-text mb-2">
                                    <strong><i class="far fa-calendar me-1"></i> Travel Date:</strong> <?php echo date('F j, Y', strtotime($booking['travel_date'])); ?>
                                </p>
                                <p class="card-text mb-2">
                                    <strong><i class="fas fa-users me-1"></i> Travelers:</strong> <?php echo $booking['num_travelers']; ?>
                                </p>
                                <p class="card-text mb-2">
                                    <strong><i class="fas fa-money-bill-wave me-1"></i> Total Price:</strong> <?php echo number_format($booking['total_price']); ?>
                                </p>
                                <p class="card-text mb-0">
                                    <strong><i class="fas fa-credit-card me-1"></i> Payment:</strong> 
                                    <span class="booking-status status-<?php echo ($booking['payment_status'] == 'completed') ? 'completed' : 'pending'; ?>">
                                        <?php echo ucfirst($booking['payment_status']); ?>
                                    </span>
                                </p>
                            </div>
                            
                            <div class="booking-actions mt-auto">
                                <?php if ($booking['status'] == 'pending'): ?>
                                    <a href="my_bookings.php?cancel=<?php echo $booking['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this booking?')">
                                        <i class="fas fa-times-circle me-1"></i> Cancel
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($booking['destination_id']): ?>
                                    <a href="destination_details.php?id=<?php echo $booking['destination_id']; ?>" class="btn btn-primary">
                                        <i class="fas fa-eye me-1"></i> View Destination
                                    </a>
                                <?php else: ?>
                                    <a href="package_details.php?id=<?php echo $booking['package_id']; ?>" class="btn btn-primary">
                                        <i class="fas fa-eye me-1"></i> View Package
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <p class="mb-0"><i class="fas fa-info-circle me-2"></i> You don't have any bookings yet. <a href="destinations.php">Explore destinations</a> or <a href="packages.php">view packages</a> to book your next trip!</p>
        </div>
    <?php endif; ?>
</div>

<?php
require_once 'includes/footer.php';
?> 