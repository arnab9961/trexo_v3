<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>" href="users.php">
                    <i class="fas fa-users me-2"></i>
                    Users
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) == 'destinations.php' ? 'active' : ''; ?>" href="destinations.php">
                    <i class="fas fa-map-marker-alt me-2"></i>
                    Destinations
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) == 'packages.php' ? 'active' : ''; ?>" href="packages.php">
                    <i class="fas fa-box me-2"></i>
                    Packages
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) == 'bookings.php' ? 'active' : ''; ?>" href="bookings.php">
                    <i class="fas fa-calendar-check me-2"></i>
                    Bookings
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) == 'inquiries.php' ? 'active' : ''; ?>" href="inquiries.php">
                    <i class="fas fa-question-circle me-2"></i>
                    Inquiries
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) == 'reviews.php' ? 'active' : ''; ?>" href="reviews.php">
                    <i class="fas fa-star me-2"></i>
                    Reviews
                </a>
            </li>
            <li class="nav-item mt-3">
                <a class="nav-link text-white" href="../logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i>
                    Logout
                </a>
            </li>
        </ul>
    </div>
</nav>

<style>
.sidebar {
    min-height: 100vh;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

.sidebar .nav-link {
    font-weight: 500;
    padding: 0.75rem 1rem;
    transition: all 0.2s;
}

.sidebar .nav-link:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

.sidebar .nav-link.active {
    background-color: rgba(255, 255, 255, 0.2);
}

.sidebar .nav-link i {
    width: 20px;
    text-align: center;
}
</style> 