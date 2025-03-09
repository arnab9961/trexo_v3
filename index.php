<?php
require_once 'includes/header.php';

// Get featured destinations
$featured_destinations_query = "SELECT * FROM destinations WHERE featured = TRUE LIMIT 3";
$featured_destinations_result = mysqli_query($conn, $featured_destinations_query);

// Get featured packages
$featured_packages_query = "SELECT * FROM packages WHERE featured = TRUE LIMIT 3";
$featured_packages_result = mysqli_query($conn, $featured_packages_query);
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <h1>Discover the World with Us</h1>
        <p>Explore amazing destinations and create unforgettable memories with our premium travel packages.</p>
        <a href="packages.php" class="btn btn-light btn-lg">View Packages</a>
    </div>
</section>

<!-- Featured Destinations -->
<section class="mb-5">
    <div class="container">
        <h2 class="text-center mb-4">Featured Destinations</h2>
        <div class="row">
            <?php
            if (mysqli_num_rows($featured_destinations_result) > 0) {
                $image_count = 1;
                while ($destination = mysqli_fetch_assoc($featured_destinations_result)) {
                    // Use one of the 6 available images
                    $image_file = "destination" . $image_count . ".jpg";
                    $image_count = ($image_count % 6) + 1;
            ?>
                <div class="col-md-4">
                    <div class="card">
                        <div class="featured-badge">Featured</div>
                        <img src="images/<?php echo $image_file; ?>" class="card-img-top" alt="<?php echo $destination['name']; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $destination['name']; ?></h5>
                            <p class="card-text"><?php echo substr($destination['description'], 0, 100) . '...'; ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="price-tag">৳<?php echo number_format($destination['price']); ?></span>
                                <a href="destination_details.php?id=<?php echo $destination['id']; ?>" class="btn btn-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
                }
            } else {
                echo '<div class="col-12"><p class="text-center">No featured destinations available.</p></div>';
            }
            ?>
        </div>
        <div class="text-center mt-4">
            <a href="destinations.php" class="btn btn-outline-primary">View All Destinations</a>
        </div>
    </div>
</section>

<!-- Featured Packages -->
<section class="mb-5">
    <div class="container">
        <h2 class="text-center mb-4">Featured Packages</h2>
        <div class="row">
            <?php
            if (mysqli_num_rows($featured_packages_result) > 0) {
                $image_count = 4;
                while ($package = mysqli_fetch_assoc($featured_packages_result)) {
                    // Use one of the 6 available images
                    $image_file = "destination" . $image_count . ".jpg";
                    $image_count = ($image_count % 6) + 1;
            ?>
                <div class="col-md-4">
                    <div class="card">
                        <div class="featured-badge">Featured</div>
                        <img src="images/<?php echo $image_file; ?>" class="card-img-top" alt="<?php echo $package['name']; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $package['name']; ?></h5>
                            <p class="card-text"><?php echo substr($package['description'], 0, 100) . '...'; ?></p>
                            <p><i class="far fa-clock me-1"></i> <?php echo $package['duration']; ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="price-tag">৳<?php echo number_format($package['price']); ?></span>
                                <a href="package_details.php?id=<?php echo $package['id']; ?>" class="btn btn-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
                }
            } else {
                echo '<div class="col-12"><p class="text-center">No featured packages available.</p></div>';
            }
            ?>
        </div>
        <div class="text-center mt-4">
            <a href="packages.php" class="btn btn-outline-primary">View All Packages</a>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="mb-5 py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-4">Why Choose Us</h2>
        <div class="row">
            <div class="col-md-3 text-center mb-4">
                <div class="p-3">
                    <i class="fas fa-globe fa-3x text-primary mb-3"></i>
                    <h4>Worldwide Destinations</h4>
                    <p>Explore hundreds of exciting destinations across the globe with our comprehensive travel packages.</p>
                </div>
            </div>
            <div class="col-md-3 text-center mb-4">
                <div class="p-3">
                    <i class="fas fa-dollar-sign fa-3x text-primary mb-3"></i>
                    <h4>Best Price Guarantee</h4>
                    <p>We offer the best prices on the market with no hidden fees or unexpected charges.</p>
                </div>
            </div>
            <div class="col-md-3 text-center mb-4">
                <div class="p-3">
                    <i class="fas fa-headset fa-3x text-primary mb-3"></i>
                    <h4>24/7 Customer Support</h4>
                    <p>Our dedicated support team is available round the clock to assist you with any queries.</p>
                </div>
            </div>
            <div class="col-md-3 text-center mb-4">
                <div class="p-3">
                    <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                    <h4>Secure Booking</h4>
                    <p>Book with confidence knowing that your personal information and payments are secure.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="mb-5">
    <div class="container">
        <div class="bg-primary text-white p-5 rounded">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3>Ready to start your adventure?</h3>
                    <p class="mb-md-0">Join us today and discover the world's most beautiful destinations.</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <?php if (!is_logged_in()): ?>
                        <a href="register.php" class="btn btn-light">Sign Up Now</a>
                    <?php else: ?>
                        <a href="packages.php" class="btn btn-light">Book a Trip</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
require_once 'includes/footer.php';
?> 