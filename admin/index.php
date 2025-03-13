<?php
require_once '../includes/config.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    $_SESSION['error_message'] = 'You do not have permission to access the admin area.';
    redirect('../login.php');
}

// Handle booking status updates
if (isset($_POST['update_booking_status'])) {
    $booking_id = $_POST['booking_id'];
    $new_status = $_POST['new_status'];
    
    $update_query = "UPDATE bookings SET status = ? WHERE id = ?";
    $update_stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($update_stmt, "si", $new_status, $booking_id);
    
    if (mysqli_stmt_execute($update_stmt)) {
        $_SESSION['success_message'] = "Booking status updated successfully.";
    } else {
        $_SESSION['error_message'] = "Failed to update booking status.";
    }
    redirect('index.php');
}

// Admin credentials for admin user management
$username = 'admin';
$password = 'admin123';
$email = 'admin@tourism.com';
$full_name = 'Admin User';
$user_type = 'admin';

// Hash the password properly
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Check if admin user exists
$check_query = "SELECT * FROM users WHERE username = ?";
$check_stmt = mysqli_prepare($conn, $check_query);
mysqli_stmt_bind_param($check_stmt, "s", $username);
mysqli_stmt_execute($check_stmt);
$check_result = mysqli_stmt_get_result($check_stmt);

// Get counts for dashboard with percentage changes
$users_query = "SELECT 
                (SELECT COUNT(*) FROM users WHERE user_type = 'customer') as current_count,
                (SELECT COUNT(*) FROM users WHERE user_type = 'customer' 
                 AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)) as new_count";
$users_result = mysqli_query($conn, $users_query);
$users_data = mysqli_fetch_assoc($users_result);
$users_count = $users_data['current_count'];
$users_growth = $users_data['current_count'] > 0 ? 
                ($users_data['new_count'] / $users_data['current_count'] * 100) : 0;

$destinations_query = "SELECT COUNT(*) as count FROM destinations";
$destinations_result = mysqli_query($conn, $destinations_query);
$destinations_count = mysqli_fetch_assoc($destinations_result)['count'];

$packages_query = "SELECT COUNT(*) as count FROM packages";
$packages_result = mysqli_query($conn, $packages_query);
$packages_count = mysqli_fetch_assoc($packages_result)['count'];

$bookings_query = "SELECT 
                   (SELECT COUNT(*) FROM bookings) as total_count,
                   (SELECT COUNT(*) FROM bookings WHERE status = 'pending') as pending_count,
                   (SELECT COUNT(*) FROM bookings WHERE booking_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH)) as new_count";
$bookings_result = mysqli_query($conn, $bookings_query);
$bookings_data = mysqli_fetch_assoc($bookings_result);
$bookings_count = $bookings_data['total_count'];
$pending_bookings_count = $bookings_data['pending_count'];
$bookings_growth = $bookings_data['total_count'] > 0 ? 
                   ($bookings_data['new_count'] / $bookings_data['total_count'] * 100) : 0;

$inquiries_query = "SELECT 
                    (SELECT COUNT(*) FROM inquiries WHERE status = 'new') as new_count,
                    (SELECT COUNT(*) FROM inquiries) as total_count";
$inquiries_result = mysqli_query($conn, $inquiries_query);
$inquiries_data = mysqli_fetch_assoc($inquiries_result);
$inquiries_count = $inquiries_data['new_count'];
$total_inquiries = $inquiries_data['total_count'];

// Get recent bookings with more details
$recent_bookings_query = "SELECT b.*, u.username, u.email,
                         d.name as destination_name, d.price as destination_price,
                         p.name as package_name, p.price as package_price
                         FROM bookings b
                         JOIN users u ON b.user_id = u.id
                         LEFT JOIN destinations d ON b.destination_id = d.id
                         LEFT JOIN packages p ON b.package_id = p.id
                         ORDER BY b.booking_date DESC
                         LIMIT 5";
$recent_bookings_result = mysqli_query($conn, $recent_bookings_query);

// Get top destinations
$top_destinations_query = "SELECT d.*, COUNT(b.id) as booking_count
                          FROM destinations d
                          LEFT JOIN bookings b ON d.id = b.destination_id
                          GROUP BY d.id
                          ORDER BY booking_count DESC
                          LIMIT 5";
$top_destinations_result = mysqli_query($conn, $top_destinations_query);

// Calculate total revenue
$revenue_query = "SELECT SUM(total_price) as total_revenue,
                  SUM(CASE WHEN booking_date >= DATE_SUB(NOW(), INTERVAL 1 MONTH) 
                      THEN total_price ELSE 0 END) as monthly_revenue
                  FROM bookings WHERE status = 'confirmed'";
$revenue_result = mysqli_query($conn, $revenue_query);
$revenue_data = mysqli_fetch_assoc($revenue_result);
$total_revenue = $revenue_data['total_revenue'] ?? 0;
$monthly_revenue = $revenue_data['monthly_revenue'] ?? 0;

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
                    <div class="col-md-3 mb-4">
                        <div class="dashboard-card">
                            <div class="icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="count"><?php echo $users_count; ?></div>
                            <div class="title">Registered Users</div>
                            <?php if ($users_growth > 0): ?>
                            <div class="growth text-success">
                                <i class="fas fa-arrow-up"></i> <?php echo number_format($users_growth, 1); ?>% this month
                            </div>
                            <?php endif; ?>
                            <a href="users.php" class="btn btn-sm btn-primary mt-3">Manage Users</a>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="dashboard-card">
                            <div class="icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="count"><?php echo $destinations_count; ?></div>
                            <div class="title">Destinations</div>
                            <a href="destinations.php" class="btn btn-sm btn-primary mt-3">Manage Destinations</a>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="dashboard-card">
                            <div class="icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="count">$<?php echo number_format($total_revenue); ?></div>
                            <div class="title">Total Revenue</div>
                            <div class="subtitle">$<?php echo number_format($monthly_revenue); ?> this month</div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
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
                            <?php if ($bookings_growth > 0): ?>
                            <div class="growth text-success">
                                <i class="fas fa-arrow-up"></i> <?php echo number_format($bookings_growth, 1); ?>% this month
                            </div>
                            <?php endif; ?>
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
                            <div class="subtitle">Out of <?php echo $total_inquiries; ?> total</div>
                            <a href="inquiries.php" class="btn btn-sm btn-primary mt-3">View Inquiries</a>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Bookings -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Recent Bookings</h5>
                        <a href="bookings.php" class="btn btn-sm btn-primary">View All</a>
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
                                            <td>
                                                <?php echo $booking['username']; ?>
                                                <br>
                                                <small class="text-muted"><?php echo $booking['email']; ?></small>
                                            </td>
                                            <td>
                                                <?php
                                                if ($booking['destination_id']) {
                                                    echo $booking['destination_name'] . ' (Destination)<br>';
                                                    echo '<small class="text-muted">$' . $booking['destination_price'] . '</small>';
                                                } else {
                                                    echo $booking['package_name'] . ' (Package)<br>';
                                                    echo '<small class="text-muted">$' . $booking['package_price'] . '</small>';
                                                }
                                                ?>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($booking['travel_date'])); ?></td>
                                            <td>$<?php echo number_format($booking['total_price'], 2); ?></td>
                                            <td>
                                                <form method="post" class="d-inline">
                                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                    <select name="new_status" class="form-select form-select-sm status-select" onchange="this.form.submit()">
                                                        <option value="pending" <?php echo $booking['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="confirmed" <?php echo $booking['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                        <option value="cancelled" <?php echo $booking['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                    </select>
                                                    <input type="hidden" name="update_booking_status" value="1">
                                                </form>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="booking_details.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-info">View</a>
                                                    <a href="booking_edit.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                                </div>
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
                    </div>
                </div>

                
            </main>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../js/script.js"></script>
    <script>
    // Add confirmation for status changes
    document.querySelectorAll('.status-select').forEach(select => {
        select.addEventListener('change', function() {
            if (!confirm('Are you sure you want to change the booking status?')) {
                this.selectedIndex = this.defaultSelected;
                return false;
            }
        });
    });
    </script>
</body>
</html> 