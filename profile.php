<?php
require_once 'includes/header.php';
require_once 'includes/navbar.php';
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

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="section-title">My Profile</h2>
            <p class="text-muted">Manage your personal information and account settings</p>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center p-4">
                    <div class="mb-4">
                        <div class="profile-avatar mx-auto mb-3">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <h4 class="mb-1"><?php echo $user['full_name']; ?></h4>
                        <p class="text-muted mb-3">@<?php echo $user['username']; ?></p>
                    </div>
                    
                    <div class="profile-info mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="profile-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="ms-3 text-start">
                                <p class="mb-0"><?php echo $user['email']; ?></p>
                            </div>
                        </div>
                        
                        <?php if (!empty($user['phone'])): ?>
                        <div class="d-flex align-items-center mb-3">
                            <div class="profile-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="ms-3 text-start">
                                <p class="mb-0"><?php echo $user['phone']; ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($user['address'])): ?>
                        <div class="d-flex align-items-center mb-3">
                            <div class="profile-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="ms-3 text-start">
                                <p class="mb-0"><?php echo $user['address']; ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <a href="my_bookings.php" class="btn btn-primary">
                            <i class="fas fa-suitcase me-2"></i>My Bookings
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <!-- Profile Update Form -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4">
                    <h3 class="mb-4 border-bottom pb-3">Update Profile</h3>
                    
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
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Profile
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Change Password Form -->
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h3 class="mb-4 border-bottom pb-3">Change Password</h3>
                    
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
                        <button type="submit" name="change_password" class="btn btn-primary">
                            <i class="fas fa-key me-2"></i>Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .section-title {
        position: relative;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #f8f9fa;
    }
    
    .section-title::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 50px;
        height: 2px;
        background-color: var(--primary);
    }
    
    .profile-avatar {
        width: 100px;
        height: 100px;
        background-color: #f8f9fa;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        color: var(--primary);
        border: 3px solid var(--primary);
    }
    
    .profile-icon {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #0d6efd, #0dcaf0);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(13, 110, 253, 0.3);
    }
    
    .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
</style>

<?php
require_once 'includes/footer.php';
?> 