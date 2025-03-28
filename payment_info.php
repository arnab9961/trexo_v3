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
} else {
    require_once 'includes/functions.php';
}

$page_title = "Payment Information";

// The header.php file already includes HTML head and navbar
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-credit-card me-2"></i>Payment Information</h4>
                </div>
                <div class="card-body">
                    <?php if (!is_admin()): ?>
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <div class="card h-100 border-danger">
                                <div class="card-header bg-white">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3" style="width: 50px; height: 50px; background-color: #e31888; border-radius: 5px; display: flex; justify-content: center; align-items: center;">
                                            <span style="color: white; font-weight: bold; font-size: 16px;">bKash</span>
                                        </div>
                                        <div>
                                            <h5 class="mb-0">bKash Payment</h5>
                                            <small class="text-muted">Send Money</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h6 class="border-bottom pb-2 mb-3">Payment Details</h6>
                                    <p><strong>Account Type:</strong> Personal</p>
                                    <p><strong>bKash Number:</strong> 01XXXXXXXXX</p>
                                    <p><strong>Account Name:</strong> Trexo Tourism</p>
                                    
                                    <h6 class="border-bottom pb-2 mb-3 mt-4">How to Pay with bKash</h6>
                                    <ol>
                                        <li>Open your bKash app or dial *247#</li>
                                        <li>Select "Send Money"</li>
                                        <li>Enter our bKash number: 01XXXXXXXXX</li>
                                        <li>Enter the exact amount as shown in your booking</li>
                                        <li>Add reference: "TREX" followed by your booking ID (e.g., TREX123)</li>
                                        <li>Enter your bKash PIN to confirm</li>
                                        <li>Save the Transaction ID from the confirmation message</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card h-100 border-warning">
                                <div class="card-header bg-white">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3" style="width: 50px; height: 50px; background-color: #f45e37; border-radius: 5px; display: flex; justify-content: center; align-items: center;">
                                            <span style="color: white; font-weight: bold; font-size: 16px;">Nagad</span>
                                        </div>
                                        <div>
                                            <h5 class="mb-0">Nagad Payment</h5>
                                            <small class="text-muted">Send Money</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <h6 class="border-bottom pb-2 mb-3">Payment Details</h6>
                                    <p><strong>Account Type:</strong> Personal</p>
                                    <p><strong>Nagad Number:</strong> 01XXXXXXXXX</p>
                                    <p><strong>Account Name:</strong> Trexo Tourism</p>
                                    
                                    <h6 class="border-bottom pb-2 mb-3 mt-4">How to Pay with Nagad</h6>
                                    <ol>
                                        <li>Open your Nagad app or dial *167#</li>
                                        <li>Select "Send Money"</li>
                                        <li>Enter our Nagad number: 01XXXXXXXXX</li>
                                        <li>Enter the exact amount as shown in your booking</li>
                                        <li>Add reference: "TREX" followed by your booking ID (e.g., TREX123)</li>
                                        <li>Enter your Nagad PIN to confirm</li>
                                        <li>Save the Transaction ID from the confirmation message</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-success mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>After Payment</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Submit Payment Information</h6>
                                    <p>After sending the payment, you need to provide us with the transaction details:</p>
                                    <ol>
                                        <li>Go to the "My Bookings" page</li>
                                        <li>Find your booking and click the "Pay Now" button</li>
                                        <li>Select the payment method you used (bKash or Nagad)</li>
                                        <li>Enter the Transaction ID from the confirmation message</li>
                                        <li>Enter the mobile number you used to send the payment</li>
                                        <li>Submit the form</li>
                                    </ol>
                                </div>
                                <div class="col-md-6">
                                    <h6>Verification Process</h6>
                                    <p>Once you submit your payment information:</p>
                                    <ul>
                                        <li>Our team will verify the transaction</li>
                                        <li>This usually takes 1-2 business hours during business days</li>
                                        <li>Once verified, your booking status will be updated to "Confirmed"</li>
                                        <li>You'll receive a confirmation email</li>
                                        <li>You can check your booking status anytime in "My Bookings"</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <a href="my_bookings.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-clipboard-list me-2"></i>Go to My Bookings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?> 