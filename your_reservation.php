<td>
    <?php if ($booking['status'] == 'pending'): ?>
        <div class="payment-methods mb-3">
            <h6 class="mb-2">Payment Methods:</h6>
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-2">
                        <div class="card-body p-2">
                            <div class="d-flex align-items-center">
                                <img src="images/bkash.png" alt="bKash" class="me-2" style="width: 40px; height: 40px;">
                                <div>
                                    <h6 class="mb-0">bKash</h6>
                                    <small class="text-muted">Send Money</small>
                                </div>
                            </div>
                            <div class="mt-2">
                                <p class="mb-1"><strong>Number:</strong> 01XXXXXXXXX</p>
                                <p class="mb-1"><strong>Amount:</strong> ৳<?php echo number_format($booking['total_price'], 2); ?></p>
                                <p class="mb-0"><strong>Reference:</strong> TREX<?php echo $booking['id']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card mb-2">
                        <div class="card-body p-2">
                            <div class="d-flex align-items-center">
                                <img src="images/nagad.png" alt="Nagad" class="me-2" style="width: 40px; height: 40px;">
                                <div>
                                    <h6 class="mb-0">Nagad</h6>
                                    <small class="text-muted">Send Money</small>
                                </div>
                            </div>
                            <div class="mt-2">
                                <p class="mb-1"><strong>Number:</strong> 01XXXXXXXXX</p>
                                <p class="mb-1"><strong>Amount:</strong> ৳<?php echo number_format($booking['total_price'], 2); ?></p>
                                <p class="mb-0"><strong>Reference:</strong> TREX<?php echo $booking['id']; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
           
        </div>
        <a href="payment.php?booking_id=<?php echo $booking['id']; ?>" class="btn btn-success">
            <i class="fas fa-money-bill-wave"></i> Pay Now
        </a>
    <?php endif; ?>
    <button type="button" class="btn btn-info view-booking" 
            data-bs-toggle="modal" 
            data-bs-target="#viewBookingModal"
            data-id="<?php echo $booking['id']; ?>"
            data-package="<?php echo htmlspecialchars($booking['package_name']); ?>"
            data-date="<?php echo $booking['booking_date']; ?>"
            data-status="<?php echo $booking['status']; ?>">
        <i class="fas fa-eye"></i> View Details
    </button>
    <button type="button" class="btn btn-danger delete-booking"
            data-bs-toggle="modal"
            data-bs-target="#deleteBookingModal"
            data-id="<?php echo $booking['id']; ?>"
            data-package="<?php echo htmlspecialchars($booking['package_name']); ?>">
        <i class="fas fa-trash"></i> Cancel
    </button>
</td> 