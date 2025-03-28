<?php
require_once 'includes/header.php';
require_once 'includes/navbar.php';
// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = 'Invalid destination ID.';
    redirect('destinations.php');
}

$destination_id = (int)$_GET['id'];

// Get destination details
$destination_query = "SELECT * FROM destinations WHERE id = ?";
$stmt = mysqli_prepare($conn, $destination_query);
mysqli_stmt_bind_param($stmt, "i", $destination_id);
mysqli_stmt_execute($stmt);
$destination_result = mysqli_stmt_get_result($stmt);

// Check if destination exists
if (mysqli_num_rows($destination_result) == 0) {
    $_SESSION['error_message'] = 'Destination not found.';
    redirect('destinations.php');
}

$destination = mysqli_fetch_assoc($destination_result);

// Use custom image if available, otherwise use default image
if (!empty($destination['image'])) {
    $image_file = $destination['image'];
} else {
    // Determine which default image to use (based on ID)
    $image_file = "destination" . (($destination_id % 6) + 1) . ".jpg";
}

// Get additional images for this destination
$images_query = "SELECT * FROM destination_images WHERE destination_id = ?";
$stmt = mysqli_prepare($conn, $images_query);
mysqli_stmt_bind_param($stmt, "i", $destination_id);
mysqli_stmt_execute($stmt);
$images_result = mysqli_stmt_get_result($stmt);
$additional_images = [];
while ($img = mysqli_fetch_assoc($images_result)) {
    $additional_images[] = $img;
}

// Get reviews for this destination
$reviews_query = "SELECT r.*, u.username, u.full_name FROM reviews r 
                 JOIN users u ON r.user_id = u.id 
                 WHERE r.destination_id = ? 
                 ORDER BY r.created_at DESC";
$stmt = mysqli_prepare($conn, $reviews_query);
mysqli_stmt_bind_param($stmt, "i", $destination_id);
mysqli_stmt_execute($stmt);
$reviews_result = mysqli_stmt_get_result($stmt);

// Process booking form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_now'])) {
    // Check if user is logged in
    if (!is_logged_in()) {
        $_SESSION['error_message'] = 'Please login to book a destination.';
        redirect('login.php');
    }
    
    $user_id = $_SESSION['user_id'];
    $travel_date = sanitize_input($_POST['travel_date']);
    $num_travelers = (int)sanitize_input($_POST['num_travelers']);
    $total_price = $destination['price'] * $num_travelers;
    
    // Validate input
    if (empty($travel_date) || $num_travelers <= 0) {
        $booking_error = 'Please fill in all required fields correctly.';
    } else {
        // Insert booking
        $booking_query = "INSERT INTO bookings (user_id, destination_id, booking_date, travel_date, num_travelers, total_price) 
                         VALUES (?, ?, NOW(), ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $booking_query);
        mysqli_stmt_bind_param($stmt, "iisid", $user_id, $destination_id, $travel_date, $num_travelers, $total_price);
        
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
        // Check if user has already reviewed this destination
        $check_query = "SELECT * FROM reviews WHERE user_id = ? AND destination_id = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, "ii", $user_id, $destination_id);
        mysqli_stmt_execute($stmt);
        $check_result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            // Update existing review
            $review_query = "UPDATE reviews SET rating = ?, comment = ?, created_at = NOW() WHERE user_id = ? AND destination_id = ?";
            $stmt = mysqli_prepare($conn, $review_query);
            mysqli_stmt_bind_param($stmt, "isii", $rating, $comment, $user_id, $destination_id);
        } else {
            // Insert new review
            $review_query = "INSERT INTO reviews (user_id, destination_id, rating, comment) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $review_query);
            mysqli_stmt_bind_param($stmt, "iiis", $user_id, $destination_id, $rating, $comment);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = 'Review submitted successfully!';
            redirect('destination_details.php?id=' . $destination_id);
        } else {
            $review_error = 'Failed to submit review. Please try again later.';
        }
    }
}
?>

<div class="container">
    <div class="row mb-4">
        <div class="col-md-8">
            <img src="images/<?php echo $image_file; ?>" class="img-fluid rounded" alt="<?php echo $destination['name']; ?>">
            
            <!-- Image Gallery -->
            <?php if (!empty($additional_images)): ?>
            <div class="mt-3">
                <h4>Image Gallery</h4>
                <div class="row">
                    <?php foreach ($additional_images as $img): ?>
                    <div class="col-md-3 col-6 mb-3">
                        <a href="images/<?php echo $img['image_path']; ?>" class="gallery-image" onclick="return false;">
                            <img src="images/<?php echo $img['image_path']; ?>" class="img-fluid rounded" alt="<?php echo $destination['name']; ?> Image">
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h2><?php echo $destination['name']; ?></h2>
                    <p><i class="fas fa-map-marker-alt me-2"></i><?php echo $destination['location']; ?></p>
                    <hr>
                    <h4 class="price-tag mb-3"><?php echo number_format($destination['price']); ?> <small class="text-muted">per person</small></h4>
                    
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
                        <input type="hidden" id="base-price" value="<?php echo $destination['price']; ?>">
                        <div class="mb-3">
                            <p>Total Price: <span id="total-price"><?php echo number_format($destination['price']); ?></span></p>
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
                    <p><?php echo $destination['description']; ?></p>
                </div>
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
                    <p>No reviews yet. Be the first to review this destination!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="text-center mb-4">
        <a href="destinations.php" class="btn btn-outline-primary">Back to Destinations</a>
    </div>
</div>

<!-- Direct script include for lightbox functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Destination details page loaded');
    const galleryLinks = document.querySelectorAll('.gallery-image');
    
    // Debug gallery links
    console.log('Found gallery links:', galleryLinks.length);
    
    galleryLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Gallery link clicked:', this.href);
            
            // Get parent container or create it
            let lightbox = document.getElementById('lightbox-container');
            
            if (!lightbox) {
                lightbox = document.createElement('div');
                lightbox.id = 'lightbox-container';
                lightbox.className = 'lightbox-container';
                lightbox.innerHTML = `
                    <div class="lightbox-content">
                        <span class="lightbox-close">&times;</span>
                        <img id="lightbox-image" class="lightbox-image">
                    </div>
                `;
                document.body.appendChild(lightbox);
                
                // Add styles
                const style = document.createElement('style');
                style.textContent = `
                    .lightbox-container {
                        display: none;
                        position: fixed;
                        z-index: 9999;
                        left: 0;
                        top: 0;
                        width: 100%;
                        height: 100%;
                        background-color: rgba(0, 0, 0, 0.9);
                        transition: opacity 0.3s ease;
                    }
                    .lightbox-content {
                        position: relative;
                        margin: auto;
                        padding: 0;
                        width: 80%;
                        max-width: 1200px;
                        height: 100%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }
                    .lightbox-image {
                        max-width: 90%;
                        max-height: 80vh;
                        border: 10px solid white;
                    }
                    .lightbox-close {
                        position: absolute;
                        top: 20px;
                        right: 35px;
                        color: white;
                        font-size: 40px;
                        font-weight: bold;
                        cursor: pointer;
                    }
                `;
                document.head.appendChild(style);
                
                // Close functionality
                document.querySelector('.lightbox-close').addEventListener('click', function() {
                    lightbox.style.display = 'none';
                });
                
                lightbox.addEventListener('click', function(e) {
                    if (e.target === lightbox) {
                        lightbox.style.display = 'none';
                    }
                });
            }
            
            // Set image and show lightbox
            const img = document.getElementById('lightbox-image');
            img.src = this.href;
            lightbox.style.display = 'block';
        });
    });
});
</script>

<?php
require_once 'includes/footer.php';
?> 