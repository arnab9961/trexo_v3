<?php
require_once '../includes/config.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

if (isset($_GET['destination_id'])) {
    $destination_id = (int)$_GET['destination_id'];
    
    // Get all images for the destination
    $query = "SELECT * FROM destination_images WHERE destination_id = ? ORDER BY id";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $destination_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $images = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $images[] = $row;
    }
    
    header('Content-Type: application/json');
    echo json_encode($images);
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Missing destination_id parameter']);
}