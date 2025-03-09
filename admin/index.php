<?php
require_once '../includes/config.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    $_SESSION['error_message'] = 'You do not have permission to access the admin area.';
    redirect('../login.php');
}

// Get counts for dashboard
$users_query = "SELECT COUNT(*) as count FROM users WHERE user_type = 'customer'";
$users_result = mysqli_query($conn, $users_query);
$users_count = mysqli_fetch_assoc($users_result)['count'];

$destinations_query = "SELECT COUNT(*) as count FROM destinations";
$destinations_result = mysqli_query($conn, $destinations_query);
$destinations_count = mysqli_fetch_assoc($destinations_result)['count'];

$packages_query = "SELECT COUNT(*) as count FROM packages";
$packages_result = mysqli_query($conn, $packages_query);
$packages_count = mysqli_fetch_assoc($packages_result)['count'];

$bookings_query = "SELECT COUNT(*) as count FROM bookings";
$bookings_result = mysqli_query($conn, $bookings_query);
$bookings_count = mysqli_fetch_assoc($bookings_result)['count'];

$pending_bookings_query = "SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'";
$pending_bookings_result = mysqli_query($conn, $pending_bookings_query);
$pending_bookings_count = mysqli_fetch_assoc($pending_bookings_result)['count'];

$inquiries_query = "SELECT COUNT(*) as count FROM inquiries WHERE status = 'new'";
$inquiries_result = mysqli_query($conn, $inquiries_query);
$inquiries_count = mysqli_fetch_assoc($inquiries_result)['count'];

// Get recent bookings
$recent_bookings_query = "SELECT b.*, u.username, 
                         d.name as destination_name,
                         p.name as package_name
                         FROM bookings b
                         JOIN users u ON b.user_id = u.id
                         LEFT JOIN destinations d ON b.destination_id = d.id
                         LEFT JOIN packages p ON b.package_id = p.id
                         ORDER BY b.booking_date DESC
                         LIMIT 5";
$recent_bookings_result = mysqli_query($conn, $recent_bookings_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Tourism Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active text-white" href="index.php">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="users.php">
                                <i class="fas fa-users me-2"></i>
                                Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="destinations.php">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                Destinations
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="packages.php">
                                <i class="fas fa-box me-2"></i>
                                Packages
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="bookings.php">
                                <i class="fas fa-calendar-check me-2"></i>
                                Bookings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="inquiries.php">
                                <i class="fas fa-question-circle me-2"></i>
                                Inquiries
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="reviews.php">
                                <i class="fas fa-star me-2"></i>
                                Reviews
                            </a>
                        </li>
                        <li class="nav-item mt-5">
                            <a class="nav-link text-white" href="../index.php">
                                <i class="fas fa-home me-2"></i>
                                Back to Website
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="../logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Admin Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="../index.php" class="btn btn-sm btn-outline-secondary">View Website</a>
                        </div>
                    </div>
                </div>
                
                <?php
                if (isset($_SESSION['success_message'])) {
                    echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
                    unset($_SESSION['success_message']);
                }
                
                if (isset($_SESSION['error_message'])) {
                    echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
                    unset($_SESSION['error_message']);
                }
                ?>
                
                <!-- Dashboard Stats -->
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="dashboard-card">
                            <div class="icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="count"><?php echo $users_count; ?></div>
                            <div class="title">Registered Users</div>
                            <a href="users.php" class="btn btn-sm btn-primary mt-3">Manage Users</a>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="dashboard-card">
                            <div class="icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="count"><?php echo $destinations_count; ?></div>
                            <div class="title">Destinations</div>
                            <a href="destinations.php" class="btn btn-sm btn-primary mt-3">Manage Destinations</a>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="dashboard-card">
                            <div class="icon">
                                <i class="fas fa-box"></i>
                            </div>
                            <div class="count"><?php echo $packages_count; ?></div>
                            <div class="title">Packages</div>
                            <a href="packages.php" class="btn btn-sm btn-primary mt-3">Manage Packages</a>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="dashboard-card">
                            <div class="icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="count"><?php echo $bookings_count; ?></div>
                            <div class="title">Total Bookings</div>
                            <a href="bookings.php" class="btn btn-sm btn-primary mt-3">View All Bookings</a>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="dashboard-card">
                            <div class="icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="count"><?php echo $pending_bookings_count; ?></div>
                            <div class="title">Pending Bookings</div>
                            <a href="bookings.php?status=pending" class="btn btn-sm btn-primary mt-3">View Pending</a>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="dashboard-card">
                            <div class="icon">
                                <i class="fas fa-question-circle"></i>
                            </div>
                            <div class="count"><?php echo $inquiries_count; ?></div>
                            <div class="title">New Inquiries</div>
                            <a href="inquiries.php" class="btn btn-sm btn-primary mt-3">View Inquiries</a>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Bookings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Bookings</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Destination/Package</th>
                                        <th>Travel Date</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (mysqli_num_rows($recent_bookings_result) > 0) {
                                        while ($booking = mysqli_fetch_assoc($recent_bookings_result)) {
                                    ?>
                                        <tr>
                                            <td><?php echo $booking['id']; ?></td>
                                            <td><?php echo $booking['username']; ?></td>
                                            <td>
                                                <?php
                                                if ($booking['destination_id']) {
                                                    echo $booking['destination_name'] . ' (Destination)';
                                                } else {
                                                    echo $booking['package_name'] . ' (Package)';
                                                }
                                                ?>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($booking['travel_date'])); ?></td>
                                            <td>$<?php echo $booking['total_price']; ?></td>
                                            <td>
                                                <span class="badge <?php echo ($booking['status'] == 'confirmed') ? 'bg-success' : (($booking['status'] == 'cancelled') ? 'bg-danger' : 'bg-warning'); ?>">
                                                    <?php echo ucfirst($booking['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="booking_details.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-info">View</a>
                                            </td>
                                        </tr>
                                    <?php
                                        }
                                    } else {
                                        echo '<tr><td colspan="7" class="text-center">No bookings found.</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end">
                            <a href="bookings.php" class="btn btn-primary">View All Bookings</a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../js/script.js"></script>
</body>
</html> 