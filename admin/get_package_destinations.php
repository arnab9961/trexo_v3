<?php
require_once '../includes/config.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Check if package_id is provided
if (!isset($_GET['package_id']) || empty($_GET['package_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Package ID is required']);
    exit;
}

$package_id = (int)$_GET['package_id'];

// Get destinations for the package
$query = "SELECT destination_id FROM package_destinations WHERE package_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $package_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$destinations = [];
while ($row = mysqli_fetch_assoc($result)) {
    $destinations[] = (int)$row['destination_id'];
}

// Return destinations as JSON
header('Content-Type: application/json');
echo json_encode($destinations); 