<?php
require_once '../includes/config.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    $_SESSION['error_message'] = 'You do not have permission to access the admin area.';
    redirect('../login.php');
}

// Handle booking actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $id = sanitize_input($_POST['id']);
        
        switch ($action) {
            case 'update_status':
                $status = sanitize_input($_POST['status']);
                $query = "UPDATE bookings SET status = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "si", $status, $id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['success_message'] = "Booking status updated successfully.";
                } else {
                    $_SESSION['error_message'] = "Error updating booking status: " . mysqli_error($conn);
                }
                break;

            case 'update_payment':
                $payment_status = sanitize_input($_POST['payment_status']);
                $query = "UPDATE bookings SET payment_status = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "si", $payment_status, $id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['success_message'] = "Payment status updated successfully.";
                } else {
                    $_SESSION['error_message'] = "Error updating payment status: " . mysqli_error($conn);
                }
                break;

            case 'delete':
                $query = "DELETE FROM bookings WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "i", $id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['success_message'] = "Booking deleted successfully.";
                } else {
                    $_SESSION['error_message'] = "Error deleting booking: " . mysqli_error($conn);
                }
                break;
        }
        
        redirect('bookings.php');
    }
}

// Get bookings list with search and pagination
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

$where_clause = $search ? "WHERE b.id LIKE '%$search%' OR u.username LIKE '%$search%' OR u.full_name LIKE '%$search%' OR u.email LIKE '%$search%' OR u.phone LIKE '%$search%'" : "";
$count_query = "SELECT COUNT(*) as total FROM bookings b 
                JOIN users u ON b.user_id = u.id 
                $where_clause";
$count_result = mysqli_query($conn, $count_query);
$total_items = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_items / $items_per_page);

$query = "SELECT b.*, u.username, u.full_name, u.email, u.phone, u.address, u.created_at as user_created_at,
          p.name as package_name, p.price as package_price,
          d.name as destination_name, d.price as destination_price,
          py.payment_method, py.transaction_id, py.sender_number, py.payment_date
          FROM bookings b 
          JOIN users u ON b.user_id = u.id
          LEFT JOIN packages p ON b.package_id = p.id
          LEFT JOIN destinations d ON b.destination_id = d.id
          LEFT JOIN (
              SELECT * FROM payments 
              WHERE id IN (
                  SELECT MAX(id) 
                  FROM payments 
                  GROUP BY booking_id
              )
          ) py ON b.id = py.booking_id
          $where_clause
          ORDER BY b.booking_date DESC 
          LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $items_per_page, $offset);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Management - Tourism Management System</title>
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
            <?php include 'includes/sidebar.php'; ?>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Booking Management</h1>
                </div>

                <?php
                if (isset($_SESSION['success_message'])) {
                    echo display_success($_SESSION['success_message']);
                    unset($_SESSION['success_message']);
                }
                if (isset($_SESSION['error_message'])) {
                    echo display_error($_SESSION['error_message']);
                    unset($_SESSION['error_message']);
                }
                ?>

                <!-- Search Form -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="search" placeholder="Search bookings..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary">Search</button>
                                <?php if ($search): ?>
                                    <a href="bookings.php" class="btn btn-secondary">Clear</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Bookings Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Customer Details</th>
                                        <th>Package/Destination</th>
                                        <th>Travel Info</th>
                                        <th>Payment Details</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($booking = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?php echo $booking['id']; ?></td>
                                        <td>
                                            <strong class="d-block"><?php echo htmlspecialchars($booking['full_name']); ?></strong>
                                            <small class="d-block"><i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($booking['username']); ?></small>
                                            <small class="d-block"><i class="fas fa-envelope me-1"></i> <?php echo htmlspecialchars($booking['email']); ?></small>
                                            <small class="d-block"><i class="fas fa-phone me-1"></i> <?php echo htmlspecialchars($booking['phone']); ?></small>
                                            <?php if ($booking['address']): ?>
                                            <small class="d-block"><i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($booking['address']); ?></small>
                                            <?php endif; ?>
                                            <small class="d-block text-muted"><i class="fas fa-clock me-1"></i> Member since: <?php echo date('M d, Y', strtotime($booking['user_created_at'])); ?></small>
                                        </td>
                                        <td>
                                            <?php if ($booking['package_id']): ?>
                                                <strong class="d-block"><?php echo htmlspecialchars($booking['package_name']); ?> (Package)</strong>
                                                <small class="d-block text-muted">Base Price: ৳<?php echo number_format($booking['package_price'], 2); ?></small>
                                            <?php else: ?>
                                                <strong class="d-block"><?php echo htmlspecialchars($booking['destination_name']); ?> (Destination)</strong>
                                                <small class="d-block text-muted">Base Price: ৳<?php echo number_format($booking['destination_price'], 2); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small class="d-block"><i class="fas fa-calendar me-1"></i> Travel: <?php echo date('M d, Y', strtotime($booking['travel_date'])); ?></small>
                                            <small class="d-block"><i class="fas fa-users me-1"></i> Travelers: <?php echo $booking['num_travelers']; ?></small>
                                            <small class="d-block"><i class="fas fa-calendar-plus me-1"></i> Booked: <?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></small>
                                        </td>
                                        <td>
                                            <strong class="d-block">৳<?php echo number_format($booking['total_price'], 2); ?></strong>
                                            <?php if ($booking['payment_method']): ?>
                                            <small class="d-block"><i class="fas fa-money-bill me-1"></i> <?php echo ucfirst($booking['payment_method']); ?></small>
                                            <small class="d-block"><i class="fas fa-receipt me-1"></i> TxID: <?php echo $booking['transaction_id']; ?></small>
                                            <small class="d-block"><i class="fas fa-phone me-1"></i> From: <?php echo $booking['sender_number']; ?></small>
                                            <small class="d-block"><i class="fas fa-clock me-1"></i> <?php echo date('M d, Y g:i A', strtotime($booking['payment_date'])); ?></small>
                                            <?php endif; ?>
                                            <form method="POST" class="mt-2">
                                                <input type="hidden" name="action" value="update_payment">
                                                <input type="hidden" name="id" value="<?php echo $booking['id']; ?>">
                                                <select name="payment_status" class="form-select form-select-sm payment-select" onchange="this.form.submit()">
                                                    <option value="pending" <?php echo $booking['payment_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="completed" <?php echo $booking['payment_status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td>
                                            <form method="POST" action="update_booking_status.php">
                                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                <select name="new_status" class="form-select form-select-sm status-select mb-2" onchange="this.form.submit()">
                                                    <option value="pending" <?php echo $booking['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                    <option value="confirmed" <?php echo $booking['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                    <option value="completed" <?php echo $booking['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                    <option value="cancelled" <?php echo $booking['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                </select>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo ($page - 1); ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Previous</a>
                                </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo ($page + 1); ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Next</a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Delete Booking Modal -->
    <div class="modal fade" id="deleteBookingModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Booking</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the booking for <strong id="delete_name"></strong>?</p>
                    <p class="text-danger">This action cannot be undone!</p>
                </div>
                <div class="modal-footer">
                    <form method="POST">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete_id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Booking</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script>
    // Handle Delete Booking Modal
    document.querySelectorAll('.delete-booking').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('delete_id').value = this.dataset.id;
            document.getElementById('delete_name').textContent = this.dataset.name;
        });
    });

    // Add confirmation for status changes
    document.querySelectorAll('.status-select').forEach(select => {
        select.addEventListener('change', function(e) {
            if (!confirm('Are you sure you want to update the booking status?')) {
                e.preventDefault();
                this.value = this.getAttribute('data-original');
                return false;
            }
            this.setAttribute('data-original', this.value);
        });
    });

    // Add confirmation for payment status changes
    document.querySelectorAll('.payment-select').forEach(select => {
        select.addEventListener('change', function(e) {
            if (!confirm('Are you sure you want to update the payment status?')) {
                e.preventDefault();
                this.value = this.getAttribute('data-original');
                return false;
            }
            this.setAttribute('data-original', this.value);
        });
    });
    </script>
</body>
</html>