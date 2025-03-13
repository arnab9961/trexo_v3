<?php
require_once 'includes/header.php';
require_once 'includes/navbar.php';
// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = 'Invalid package ID.';
    redirect('packages.php');
}

$package_id = (int)$_GET['id'];

// Get package details
$package_query = "SELECT * FROM packages WHERE id = ?";
$stmt = mysqli_prepare($conn, $package_query);
mysqli_stmt_bind_param($stmt, "i", $package_id);
mysqli_stmt_execute($stmt);
$package_result = mysqli_stmt_get_result($stmt);

// Check if package exists
if (mysqli_num_rows($package_result) == 0) {
    $_SESSION['error_message'] = 'Package not found.';
    redirect('packages.php');
}

$package = mysqli_fetch_assoc($package_result);

// Use custom image if available, otherwise use default image
if (!empty($package['image'])) {
    $image_file = $package['image'];
} else {
    // Determine which default image to use (based on ID)
    $image_file = "destination" . (($package_id % 6) + 1) . ".jpg";
}

// Get destinations included in this package
$destinations_query = "SELECT d.* FROM destinations d 
                      JOIN package_destinations pd ON d.id = pd.destination_id 
                      WHERE pd.package_id = ?";
$stmt = mysqli_prepare($conn, $destinations_query);
mysqli_stmt_bind_param($stmt, "i", $package_id);
mysqli_stmt_execute($stmt);
$destinations_result = mysqli_stmt_get_result($stmt);

// Get reviews for this package
$reviews_query = "SELECT r.*, u.username, u.full_name FROM reviews r 
                 JOIN users u ON r.user_id = u.id 
                 WHERE r.package_id = ? 
                 ORDER BY r.created_at DESC";
$stmt = mysqli_prepare($conn, $reviews_query);
mysqli_stmt_bind_param($stmt, "i", $package_id);
mysqli_stmt_execute($stmt);
$reviews_result = mysqli_stmt_get_result($stmt);

// Process booking form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_now'])) {
    // Check if user is logged in
    if (!is_logged_in()) {
        $_SESSION['error_message'] = 'Please login to book a package.';
        redirect('login.php');
    }
    
    $user_id = $_SESSION['user_id'];
    $travel_date = sanitize_input($_POST['travel_date']);
    $num_travelers = (int)sanitize_input($_POST['num_travelers']);
    $total_price = $package['price'] * $num_travelers;
    
    // Validate input
    if (empty($travel_date) || $num_travelers <= 0) {
        $booking_error = 'Please fill in all required fields correctly.';
    } else {
        // Insert booking
        $booking_query = "INSERT INTO bookings (user_id, package_id, booking_date, travel_date, num_travelers, total_price) 
                         VALUES (?, ?, NOW(), ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $booking_query);
        mysqli_stmt_bind_param($stmt, "iisid", $user_id, $package_id, $travel_date, $num_travelers, $total_price);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = 'Booking successful! You can view your booking details in your profile.';
            redirect('my_bookings.php');
        } else {
            $booking_error = 'Booking failed. Please try again later.';
        }
    }
}

// Process review form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    // Check if user is logged in
    if (!is_logged_in()) {
        $_SESSION['error_message'] = 'Please login to submit a review.';
        redirect('login.php');
    }
    
    $user_id = $_SESSION['user_id'];
    $rating = (int)sanitize_input($_POST['rating']);
    $comment = sanitize_input($_POST['comment']);
    
    // Validate input
    if ($rating < 1 || $rating > 5 || empty($comment)) {
        $review_error = 'Please provide both rating and comment.';
    } else {
        // Check if user has already reviewed this package
        $check_query = "SELECT * FROM reviews WHERE user_id = ? AND package_id = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, "ii", $user_id, $package_id);
        mysqli_stmt_execute($stmt);
        $check_result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            // Update existing review
            $review_query = "UPDATE reviews SET rating = ?, comment = ?, created_at = NOW() WHERE user_id = ? AND package_id = ?";
            $stmt = mysqli_prepare($conn, $review_query);
            mysqli_stmt_bind_param($stmt, "isii", $rating, $comment, $user_id, $package_id);
        } else {
            // Insert new review
            $review_query = "INSERT INTO reviews (user_id, package_id, rating, comment) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $review_query);
            mysqli_stmt_bind_param($stmt, "iiis", $user_id, $package_id, $rating, $comment);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = 'Review submitted successfully!';
            redirect('package_details.php?id=' . $package_id);
        } else {
            $review_error = 'Failed to submit review. Please try again later.';
        }
    }
}
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <img src="images/<?php echo $image_file; ?>" class="img-fluid rounded" alt="<?php echo $package['name']; ?>">
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h2><?php echo $package['name']; ?></h2>
                    <p><i class="far fa-clock me-2"></i><?php echo $package['duration']; ?></p>
                    <hr>
                    <h4 class="price-tag mb-3"><?php echo number_format($package['price']); ?> <small class="text-muted">per person</small></h4>
                    
                    <!-- Booking Form -->
                    <form method="POST" action="">
                        <?php if (isset($booking_error)): ?>
                            <?php echo display_error($booking_error); ?>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="travel_date" class="form-label">Travel Date</label>
                            <input type="date" class="form-control" id="travel_date" name="travel_date" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="num_travelers" class="form-label">Number of Travelers</label>
                            <input type="number" class="form-control" id="num_travelers" name="num_travelers" min="1" value="1" required>
                        </div>
                        <input type="hidden" id="base-price" value="<?php echo $package['price']; ?>">
                        <div class="mb-3">
                            <p>Total Price: <span id="total-price"><?php echo number_format($package['price']); ?></span></p>
                        </div>
                        <div class="d-grid">
                            <button type="submit" name="book_now" class="btn btn-primary">Book Now</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-5">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h3>Description</h3>
                    <p><?php echo $package['description']; ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Included Destinations -->
    <div class="row mb-5">
        <div class="col-12">
            <h3>Included Destinations</h3>
            <div class="row">
                <?php
                if (mysqli_num_rows($destinations_result) > 0) {
                    $dest_count = 1;
                    while ($destination = mysqli_fetch_assoc($destinations_result)) {
                        // Use custom image if available, otherwise use default image
                        if (!empty($destination['image'])) {
                            $dest_image = $destination['image'];
                        } else {
                            // Use one of the 6 available images
                            $dest_image = "destination" . $dest_count . ".jpg";
                            $dest_count = ($dest_count % 6) + 1;
                        }
                ?>
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <img src="images/<?php echo $dest_image; ?>" class="card-img-top" alt="<?php echo $destination['name']; ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $destination['name']; ?></h5>
                                <p><i class="fas fa-map-marker-alt me-2"></i><?php echo $destination['location']; ?></p>
                                <a href="destination_details.php?id=<?php echo $destination['id']; ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php
                    }
                } else {
                    echo '<div class="col-12"><p class="alert alert-info">No destinations included in this package.</p></div>';
                }
                ?>
            </div>
        </div>
    </div>
    
    <!-- Reviews Section -->
    <div class="row mb-5">
        <div class="col-12">
            <h3>Reviews</h3>
            
            <!-- Review Form -->
            <?php if (is_logged_in()): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <h4>Write a Review</h4>
                        
                        <?php if (isset($review_error)): ?>
                            <?php echo display_error($review_error); ?>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="rating" class="form-label">Rating</label>
                                <select class="form-select" id="rating" name="rating" required>
                                    <option value="">Select Rating</option>
                                    <option value="5">5 - Excellent</option>
                                    <option value="4">4 - Very Good</option>
                                    <option value="3">3 - Good</option>
                                    <option value="2">2 - Fair</option>
                                    <option value="1">1 - Poor</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="comment" class="form-label">Comment</label>
                                <textarea class="form-control" id="comment" name="comment" rows="3" required></textarea>
                            </div>
                            <button type="submit" name="submit_review" class="btn btn-primary">Submit Review</button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info mb-4">
                    <p>Please <a href="login.php">login</a> to write a review.</p>
                </div>
            <?php endif; ?>
            
            <!-- Reviews List -->
            <?php if (mysqli_num_rows($reviews_result) > 0): ?>
                <?php while ($review = mysqli_fetch_assoc($reviews_result)): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <h5><?php echo $review['full_name']; ?> <small class="text-muted">(@<?php echo $review['username']; ?>)</small></h5>
                                <div class="rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= $review['rating']): ?>
                                            <i class="fas fa-star"></i>
                                        <?php else: ?>
                                            <i class="far fa-star"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <p class="text-muted small">Posted on <?php echo date('F j, Y', strtotime($review['created_at'])); ?></p>
                            <p><?php echo $review['comment']; ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    <p>No reviews yet. Be the first to review this package!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="text-center mb-4">
        <a href="packages.php" class="btn btn-outline-primary">Back to Packages</a>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?> 