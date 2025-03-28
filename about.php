<?php
require_once 'includes/header.php';
require_once 'includes/navbar.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <h1>About Us</h1>
        <p>Learn more about TREXO and our mission to create unforgettable travel experiences.</p>
    </div>
</section>

<!-- Main Content -->
<main>
    <!-- Our Story -->
    <section class="mb-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 mb-4 mb-md-0">
                    <img src="images/Cover.jpg" class="img-fluid rounded shadow" alt="Tourism">
                    <div class="text-center mt-2">
                        <small class="text-muted">Experience the beauty of tourism</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <h2 class="text-center mb-8">Our Story</h2>
                    <p>TREXO was founded in 2025 with a simple mission: to make travel accessible, enjoyable, and memorable for everyone. What started as a small team of travel enthusiasts has grown into a comprehensive platform connecting travelers with amazing destinations worldwide.</p>
                    <p>We believe that travel is not just about visiting new places, but about creating meaningful experiences and connections. Our team works tirelessly to curate the best destinations and packages, ensuring that every journey with us is special.</p>
                </div>
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
</main>

<?php
require_once 'includes/footer.php';
?> 