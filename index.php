<?php
require_once 'includes/header.php';
require_once 'includes/navbar.php';

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
        <a href="packages.php" class="btn btn-primary">View Packages</a>
    </div>
</section>

<!-- Main Content -->
<main>
    <!-- Featured Destinations -->
    <section class="mb-5">
        <div class="container">
            <h2 class="text-center mb-4">Featured Destinations</h2>
            <div class="row">
                <?php
                if (mysqli_num_rows($featured_destinations_result) > 0) {
                    $image_count = 1;
                    while ($destination = mysqli_fetch_assoc($featured_destinations_result)) {
                        // Use custom image if available, otherwise use default image
                        if (!empty($destination['image'])) {
                            $image_file = $destination['image'];
                        } else {
                            // Use one of the 6 available images
                            $image_file = "destination" . $image_count . ".jpg";
                            $image_count = ($image_count % 6) + 1;
                        }
                ?>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="featured-badge">Featured</div>
                            <img src="images/<?php echo $image_file; ?>" class="card-img-top" alt="<?php echo $destination['name']; ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $destination['name']; ?></h5>
                                <p class="card-text"><?php echo substr($destination['description'], 0, 100) . '...'; ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="price-tag"><?php echo number_format($destination['price']); ?></span>
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
                        // Use custom image if available, otherwise use default image
                        if (!empty($package['image'])) {
                            $image_file = $package['image'];
                        } else {
                            // Use one of the 6 available images
                            $image_file = "destination" . $image_count . ".jpg";
                            $image_count = ($image_count % 6) + 1;
                        }
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
                                    <span class="price-tag"><?php echo number_format($package['price']); ?></span>
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

    <!-- Our Leadership Team -->
    <section class="mb-5">
        <div class="container">
            <h2 class="text-center mb-4">Our Leadership Team</h2>
            <p class="lead text-center mb-5">Meet the dedicated professionals who make our travel experiences exceptional</p>
            
            <div class="row justify-content-center">
                <div class="col-md-4 mb-4">
                    <div class="card team-card h-100 border shadow">
                        <div class="team-img-container">
                            <img src="images/CEO.jpg" class="card-img-top team-img" alt="MD Arif Al amin - CEO">
                        </div>
                        <div class="card-body text-center py-4">
                            <h4 class="card-title">Md. Arif Shaikh</h4>
                            <p class="text-primary fw-bold">Chief Executive Officer</p>
                            <p class="card-text"><i class="fas fa-envelope me-2"></i>arifsh932@gmail.com</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card team-card h-100 border shadow">
                        <div class="team-img-container">
                            <img src="images/COO.jpg" class="card-img-top team-img" alt="Enamul Haque Nabin - COO">
                        </div>
                        <div class="card-body text-center py-4">
                            <h4 class="card-title">Enamul Haque Nabin</h4>
                            <p class="text-primary fw-bold">Chief Operating Officer</p>
                            <p class="card-text"><i class="fas fa-envelope me-2"></i>enobin9@gmail.com</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card team-card h-100 border shadow">
                        <div class="team-img-container">
                            <img src="images/CA.jpg" class="card-img-top team-img" alt="Mahmodul Hasan - Chief Accountant">
                        </div>
                        <div class="card-body text-center py-4">
                            <h4 class="card-title">Mahmodul Hasan</h4>
                            <p class="text-primary fw-bold">Chief Accountant</p>
                            <p class="card-text"><i class="fas fa-envelope me-2"></i>mahmodul420@gmail.com</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
require_once 'includes/footer.php';
?> 