<?php
require_once 'config.php';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tourism Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/navbar.css">
    <style>
        :root {
            --primary: #0d6efd;
            --secondary: #6c757d;
            --light: #f8f9fa;
            --dark: #212529;
            --danger: #dc3545;
        }
        body {
            padding-top: 76px; /* Fixed navbar padding */
        }
    </style>
</head>
<body>
    <?php
    // Display messages if they exist
    if (isset($_SESSION['success_message']) || isset($_SESSION['error_message'])) {
    ?>
    <div class="container mt-4">
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
    </div>
    <?php } ?>
</body>
</html> 