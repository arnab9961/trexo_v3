<?php
require_once 'includes/config.php';

// Define SQL to create payments table
$sql = "CREATE TABLE IF NOT EXISTS payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    payment_method ENUM('bkash', 'nagad') NOT NULL,
    transaction_id VARCHAR(100) NOT NULL,
    sender_number VARCHAR(20) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
)";

// Execute query
if (mysqli_query($conn, $sql)) {
    echo "Payments table created successfully!";
    
    // Check if bookings table has payment_status column
    $check_column = "SHOW COLUMNS FROM bookings LIKE 'payment_status'";
    $column_exists = mysqli_query($conn, $check_column);
    
    if (mysqli_num_rows($column_exists) == 0) {
        // Add payment_status column to bookings table
        $add_column = "ALTER TABLE bookings ADD COLUMN payment_status VARCHAR(20) DEFAULT 'pending'";
        if (mysqli_query($conn, $add_column)) {
            echo "<br>Payment status column added to bookings table.";
        } else {
            echo "<br>Error adding payment status column: " . mysqli_error($conn);
        }
    } else {
        echo "<br>Payment status column already exists in bookings table.";
    }
} else {
    echo "Error creating payments table: " . mysqli_error($conn);
}
?> 