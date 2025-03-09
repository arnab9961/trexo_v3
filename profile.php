<?php
require_once 'includes/header.php';

// Check if user is logged in
if (!is_logged_in()) {
    $_SESSION['error_message'] = 'Please login to view your profile.';
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Get user details
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$user_result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($user_result);

// Process profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = sanitize_input($_POST['full_name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $address = sanitize_input($_POST['address']);
    $error = '';
    
    // Validate input
    if (empty($full_name) || empty($email)) {
        $error = 'Full name and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Check if email already exists (for other users)
        $email_check_query = "SELECT * FROM users WHERE email = ? AND id != ?";
        $stmt = mysqli_prepare($conn, $email_check_query);
        mysqli_stmt_bind_param($stmt, "si", $email, $user_id);
        mysqli_stmt_execute($stmt);
        $email_check_result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($email_check_result) > 0) {
            $error = 'Email already exists. Please use a different one.';
        } else {
            // Update profile
            $update_query = "UPDATE users SET full_name = ?, email = ?, phone = ?, address = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, "ssssi", $full_name, $email, $phone, $address, $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = 'Profile updated successfully.';
                redirect('profile.php');
            } else {
                $error = 'Failed to update profile. Please try again.';
            }
        }
    }
}

// Process password change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $password_error = '';
    
    // Validate input
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $password_error = 'All password fields are required.';
    } elseif ($new_password != $confirm_password) {
        $password_error = 'New passwords do not match.';
    } elseif (strlen($new_password) < 6) {
        $password_error = 'New password must be at least 6 characters long.';
    } else {
        // Verify current password
        if (password_verify($current_password, $user['password'])) {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, "si", $hashed_password, $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = 'Password changed successfully.';
                redirect('profile.php');
            } else {
                $password_error = 'Failed to change password. Please try again.';
            }
        } else {
            $password_error = 'Current password is incorrect.';
        }
    }
}
?>

<div class="container">
    <h2 class="mb-4">My Profile</h2>
    
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <h4><?php echo $user['full_name']; ?></h4>
                    <p class="text-muted">@<?php echo $user['username']; ?></p>
                    <p><i class="fas fa-envelope me-2"></i><?php echo $user['email']; ?></p>
                    <?php if (!empty($user['phone'])): ?>
                        <p><i class="fas fa-phone me-2"></i><?php echo $user['phone']; ?></p>
                    <?php endif; ?>
                    <div class="d-grid gap-2">
                        <a href="my_bookings.php" class="btn btn-primary">My Bookings</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <!-- Profile Update Form -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Update Profile</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($error) && !empty($error)): ?>
                        <?php echo display_error($error); ?>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" value="<?php echo $user['username']; ?>" disabled>
                            <small class="text-muted">Username cannot be changed.</small>
                        </div>
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo $user['full_name']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="phone" name="phone" value="<?php echo $user['phone']; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3"><?php echo $user['address']; ?></textarea>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </div>
            
            <!-- Change Password Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Change Password</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($password_error) && !empty($password_error)): ?>
                        <?php echo display_error($password_error); ?>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password *</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password *</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <small class="text-muted">Password must be at least 6 characters long.</small>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password *</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?> 