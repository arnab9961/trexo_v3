<td>
    <?php if ($booking['status'] == 'pending'): ?>
        <a href="payment.php?booking_id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-success">
            <i class="fas fa-money-bill-wave"></i> Pay Now
        </a>
    <?php endif; ?>
    <button type="button" class="btn btn-sm btn-info view-booking" 
            data-bs-toggle="modal" 
            data-bs-target="#viewBookingModal"
            data-id="<?php echo $booking['id']; ?>"
            data-package="<?php echo htmlspecialchars($booking['package_name']); ?>"
            data-date="<?php echo $booking['booking_date']; ?>"
            data-status="<?php echo $booking['status']; ?>">
        <i class="fas fa-eye"></i>
    </button>
    <button type="button" class="btn btn-sm btn-danger delete-booking"
            data-bs-toggle="modal"
            data-bs-target="#deleteBookingModal"
            data-id="<?php echo $booking['id']; ?>"
            data-package="<?php echo htmlspecialchars($booking['package_name']); ?>">
        <i class="fas fa-trash"></i>
    </button>
</td> 