<?php
require_once 'includes/header.php';
require_once 'includes/navbar.php';
// Check if user is logged in
if (!is_logged_in()) {
    $_SESSION['error_message'] = 'Please login to view your bookings.';
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$is_admin = isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'admin';

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
if ($is_admin) {
    // Admin sees all bookings with more details
    $bookings_query = "SELECT b.*, 
                      u.username, u.email, u.full_name, u.phone,
                      d.name as destination_name, d.image as destination_image,
                      p.name as package_name, p.image as package_image,
                      py.payment_method, py.transaction_id, py.sender_number, py.payment_date
                      FROM bookings b
                      LEFT JOIN destinations d ON b.destination_id = d.id
                      LEFT JOIN packages p ON b.package_id = p.id
                      LEFT JOIN users u ON b.user_id = u.id
                      LEFT JOIN (
                          SELECT * FROM payments 
                          WHERE id IN (
                              SELECT MAX(id) 
                              FROM payments 
                              GROUP BY booking_id
                          )
                      ) py ON b.id = py.booking_id
                      ORDER BY b.booking_date DESC";
    $stmt = mysqli_prepare($conn, $bookings_query);
} else {
    // Regular users see only their bookings
    $bookings_query = "SELECT b.*, 
                      d.name as destination_name, d.image as destination_image,
                      p.name as package_name, p.image as package_image,
                      py.payment_method, py.transaction_id, py.sender_number, py.payment_date
                      FROM bookings b
                      LEFT JOIN destinations d ON b.destination_id = d.id
                      LEFT JOIN packages p ON b.package_id = p.id
                      LEFT JOIN (
                          SELECT * FROM payments 
                          WHERE id IN (
                              SELECT MAX(id) 
                              FROM payments 
                              GROUP BY booking_id
                          )
                      ) py ON b.id = py.booking_id
                      WHERE b.user_id = ?
                      ORDER BY b.booking_date DESC";
    $stmt = mysqli_prepare($conn, $bookings_query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
}
mysqli_stmt_execute($stmt);
$bookings_result = mysqli_stmt_get_result($stmt);
?>

<div class="container py-5">
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success_message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $_SESSION['error_message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
    
    <?php if (mysqli_num_rows($bookings_result) > 0): ?>
        <h2 class="mb-4"><?php echo $is_admin ? 'All Bookings' : 'Your Reservations'; ?></h2>
        <div class="row">
            <?php while ($booking = mysqli_fetch_assoc($bookings_result)): ?>
                <div class="col-lg-6 col-md-12 mb-4">
                    <div class="card booking-details">
                        <?php if ($booking['destination_id'] && $booking['destination_image']): ?>
                            <div class="booking-image-container">
                                <img src="images/<?php echo $booking['destination_image']; ?>" class="booking-image" alt="<?php echo $booking['destination_name']; ?>">
                            </div>
                        <?php elseif ($booking['package_id'] && $booking['package_image']): ?>
                            <div class="booking-image-container">
                                <img src="images/<?php echo $booking['package_image']; ?>" class="booking-image" alt="<?php echo $booking['package_name']; ?>">
                            </div>
                        <?php else: ?>
                            <div class="booking-image-container">
                                <img src="images/placeholder.jpg" class="booking-image" alt="Booking Image">
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
                            
                            <?php if ($is_admin && isset($booking['username'])): ?>
                                <div class="booking-user-info mb-2" style="background-color: #e3f2fd; border: 1px solid #90caf9; border-radius: 4px; padding: 8px; margin-top: 10px;">
                                    <small>
                                        <strong><i class="fas fa-user me-1"></i> Booked by:</strong> <?php echo htmlspecialchars($booking['username']); ?><br>
                                        <strong><i class="fas fa-envelope me-1"></i> Email:</strong> <?php echo htmlspecialchars($booking['email']); ?><br>
                                        <strong><i class="fas fa-phone me-1"></i> Phone:</strong> <?php echo htmlspecialchars($booking['phone']); ?>
                                    </small>
                                </div>
                                
                                <?php if (isset($booking['payment_method'])): ?>
                                    <div class="booking-payment-info mb-2" style="background-color: #fff3e0; border: 1px solid #ffcc80; border-radius: 4px; padding: 8px; margin-top: 10px;">
                                        <small>
                                            <strong><i class="fas fa-money-bill-wave me-1"></i> Payment Details:</strong><br>
                                            <strong>Method:</strong> <?php echo ucfirst($booking['payment_method']); ?><br>
                                            <strong>Transaction ID:</strong> <?php echo htmlspecialchars($booking['transaction_id']); ?><br>
                                            <strong>Sender Number:</strong> <?php echo htmlspecialchars($booking['sender_number']); ?><br>
                                            <strong>Payment Date:</strong> <?php echo date('F j, Y g:i A', strtotime($booking['payment_date'])); ?>
                                        </small>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            
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
                                    <?php if ($booking['payment_status'] != 'completed' && !$is_admin): ?>
                                        <a href="payment.php?booking_id=<?php echo $booking['id']; ?>" class="btn btn-success mb-2">
                                            <i class="fas fa-money-bill-wave me-1"></i> Pay Now
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!$is_admin): ?>
                                    <a href="my_bookings.php?cancel=<?php echo $booking['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this booking?')">
                                        <i class="fas fa-times-circle me-1"></i> Cancel
                                    </a>
                                    <?php endif; ?>
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
                                
                                <?php if ($is_admin): ?>
                                    <div class="mt-3 p-2 border rounded">
                                        <h6 class="mb-2"><i class="fas fa-cog me-1"></i> Admin Controls</h6>
                                        <form action="admin/update_booking.php" method="post" class="row g-2">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <div class="col-md-6">
                                                <label class="form-label mb-0">Booking Status:</label>
                                                <select name="status" class="form-select form-select-sm">
                                                    <option value="pending" <?php echo $booking['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="confirmed" <?php echo $booking['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                    <option value="completed" <?php echo $booking['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                    <option value="cancelled" <?php echo $booking['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label mb-0">Payment Status:</label>
                                                <select name="payment_status" class="form-select form-select-sm">
                                                    <option value="pending" <?php echo $booking['payment_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="completed" <?php echo $booking['payment_status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                </select>
                                            </div>
                                            <div class="col-12 mt-2">
                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-save me-1"></i> Update
                                                </button>
                                            </div>
                                        </form>
                                    </div>
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