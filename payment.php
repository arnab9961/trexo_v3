<?php
require_once 'includes/config.php';
require_once 'includes/header.php';
require_once 'includes/navbar.php';

// Check if functions file exists, if not define the required functions here
if (!file_exists('includes/functions.php')) {
    // Basic functions needed for this page
    function is_logged_in() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    function redirect($url) {
        header("Location: $url");
        exit;
    }
    
    function sanitize_input($data) {
        global $conn;
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        if ($conn) {
            $data = mysqli_real_escape_string($conn, $data);
        }
        return $data;
    }
} else {
    require_once 'includes/functions.php';
}

// Check if user is logged in
if (!is_logged_in()) {
    $_SESSION['error_message'] = 'Please login to make a payment.';
    redirect('login.php');
}

// Check if booking ID is provided
if (!isset($_GET['booking_id'])) {
    $_SESSION['error_message'] = 'Invalid booking reference.';
    redirect('my_bookings.php');
}

$booking_id = (int)$_GET['booking_id'];

// Get booking details
$booking_query = "SELECT b.*, 
                 COALESCE(p.name, d.name) as item_name,
                 COALESCE(p.price, d.price) as item_price 
                 FROM bookings b 
                 LEFT JOIN packages p ON b.package_id = p.id 
                 LEFT JOIN destinations d ON b.destination_id = d.id 
                 WHERE b.id = ? AND b.user_id = ?";
$stmt = mysqli_prepare($conn, $booking_query);
mysqli_stmt_bind_param($stmt, "ii", $booking_id, $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$booking_result = mysqli_stmt_get_result($stmt);
$booking = mysqli_fetch_assoc($booking_result);

if (!$booking) {
    $_SESSION['error_message'] = 'Booking not found.';
    redirect('my_bookings.php');
}

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment_method = sanitize_input($_POST['payment_method']);
    $transaction_id = sanitize_input($_POST['transaction_id']);
    $sender_number = sanitize_input($_POST['sender_number']);
    
    // Validate inputs
    $errors = [];
    if (!in_array($payment_method, ['bkash', 'nagad'])) {
        $errors[] = 'Invalid payment method.';
    }
    if (empty($transaction_id)) {
        $errors[] = 'Transaction ID is required.';
    }
    if (empty($sender_number)) {
        $errors[] = 'Sender number is required.';
    }
    
    if (empty($errors)) {
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Insert payment record
            $payment_query = "INSERT INTO payments (booking_id, payment_method, transaction_id, sender_number, amount) 
                             VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $payment_query);
            mysqli_stmt_bind_param($stmt, "isssd", $booking_id, $payment_method, $transaction_id, $sender_number, $booking['total_price']);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('Error inserting payment record: ' . mysqli_error($conn));
            }
            
            // Update booking payment status
            $update_query = "UPDATE bookings SET payment_status = 'pending' WHERE id = ?";
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, "i", $booking_id);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('Error updating booking status: ' . mysqli_error($conn));
            }
            
            // Commit transaction
            mysqli_commit($conn);
            
            $_SESSION['success_message'] = 'Payment information submitted successfully. We will verify your payment and update the booking status.';
            redirect('my_bookings.php');
            
        } catch (Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($conn);
            $errors[] = 'Error processing payment: ' . $e->getMessage();
        }
    }
}

$page_title = "Payment - " . $booking['item_name'];
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Payment Information</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="mb-4">
                        <h5 class="border-bottom pb-2 mb-3">Booking Details</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong><i class="fas fa-tag me-1"></i> Item:</strong> <?php echo htmlspecialchars($booking['item_name']); ?></p>
                                <p><strong><i class="fas fa-calendar me-1"></i> Booking Date:</strong> <?php echo date('F d, Y', strtotime($booking['booking_date'])); ?></p>
                                <p><strong><i class="fas fa-users me-1"></i> Number of Travelers:</strong> <?php echo $booking['num_travelers']; ?></p>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-primary mb-0">
                                    <h5 class="mb-2">Amount to Pay:</h5>
                                    <p class="h3 mb-0">৳<?php echo number_format($booking['total_price'], 2); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" class="needs-validation" novalidate>
                        <div class="mb-4">
                            <h5 class="border-bottom pb-2 mb-3">Payment Method</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="form-check payment-option">
                                        <input class="form-check-input" type="radio" name="payment_method" id="bkash" value="bkash" required>
                                        <label class="form-check-label d-flex align-items-center" for="bkash">
                                            <div class="me-2" style="width: 40px; height: 40px; background-color: #e31888; border-radius: 5px; display: flex; justify-content: center; align-items: center;">
                                                <span style="color: white; font-weight: bold; font-size: 14px;">bKash</span>
                                            </div>
                                            <span>bKash Payment</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="form-check payment-option">
                                        <input class="form-check-input" type="radio" name="payment_method" id="nagad" value="nagad" required>
                                        <label class="form-check-label d-flex align-items-center" for="nagad">
                                            <div class="me-2" style="width: 40px; height: 40px; background-color: #f45e37; border-radius: 5px; display: flex; justify-content: center; align-items: center;">
                                                <span style="color: white; font-weight: bold; font-size: 14px;">Nagad</span>
                                            </div>
                                            <span>Nagad Payment</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="transaction_id" class="form-label">Transaction ID <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="transaction_id" name="transaction_id" required>
                            <div class="form-text">Enter the transaction ID you received after making the payment</div>
                            <div class="invalid-feedback">
                                Please provide your transaction ID.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="sender_number" class="form-label">Sender Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="sender_number" name="sender_number" required>
                            <div class="form-text">Enter the phone number you used to send the payment</div>
                            <div class="invalid-feedback">
                                Please provide your sender number.
                            </div>
                        </div>

                        

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-check-circle me-2"></i>Submit Payment Information
                            </button>
                            <a href="my_bookings.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to My Bookings
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom JS -->
<script>
// Form validation
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()
</script>

<?php
require_once 'includes/footer.php';
?> 