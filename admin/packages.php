<?php
require_once '../includes/config.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    redirect('../login.php');
}

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = sanitize_input($_POST['name']);
                $description = sanitize_input($_POST['description']);
                $price = (float)sanitize_input($_POST['price']);
                $duration = sanitize_input($_POST['duration']);
                $featured = isset($_POST['featured']) ? 1 : 0;
                
                // Handle main thumbnail image upload
                $image = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                    $filename = $_FILES['image']['name'];
                    $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    
                    if (in_array($file_ext, $allowed)) {
                        $new_filename = uniqid('package_') . '.' . $file_ext;
                        $upload_path = '../images/' . $new_filename;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                            $image = $new_filename;
                        }
                    }
                }
                
                $query = "INSERT INTO packages (name, description, price, duration, featured, image) 
                         VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "ssdssi", $name, $description, $price, $duration, $featured, $image);
                
                if (mysqli_stmt_execute($stmt)) {
                    $package_id = mysqli_insert_id($conn);
                    
                    // Handle multiple images upload
                    if (isset($_FILES['additional_images']) && is_array($_FILES['additional_images']['name'])) {
                        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                        $image_query = "INSERT INTO package_images (package_id, image_path) VALUES (?, ?)";
                        $image_stmt = mysqli_prepare($conn, $image_query);
                        
                        for ($i = 0; $i < count($_FILES['additional_images']['name']); $i++) {
                            if ($_FILES['additional_images']['error'][$i] == 0) {
                                $filename = $_FILES['additional_images']['name'][$i];
                                $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                                
                                if (in_array($file_ext, $allowed)) {
                                    $new_filename = uniqid('package_img_') . '.' . $file_ext;
                                    $upload_path = '../images/' . $new_filename;
                                    
                                    if (move_uploaded_file($_FILES['additional_images']['tmp_name'][$i], $upload_path)) {
                                        mysqli_stmt_bind_param($image_stmt, "is", $package_id, $new_filename);
                                        mysqli_stmt_execute($image_stmt);
                                    }
                                }
                            }
                        }
                    }
                    
                    // Handle destinations if selected
                    if (isset($_POST['destinations']) && is_array($_POST['destinations'])) {
                        $dest_query = "INSERT INTO package_destinations (package_id, destination_id) VALUES (?, ?)";
                        $dest_stmt = mysqli_prepare($conn, $dest_query);
                        
                        foreach ($_POST['destinations'] as $destination_id) {
                            mysqli_stmt_bind_param($dest_stmt, "ii", $package_id, $destination_id);
                            mysqli_stmt_execute($dest_stmt);
                        }
                    }
                    
                    $_SESSION['success_message'] = "Package added successfully.";
                } else {
                    $_SESSION['error_message'] = "Error adding package: " . mysqli_error($conn);
                }
                break;
                
            case 'edit':
                $id = sanitize_input($_POST['id']);
                $name = sanitize_input($_POST['name']);
                $description = sanitize_input($_POST['description']);
                $price = (float)sanitize_input($_POST['price']);
                $duration = sanitize_input($_POST['duration']);
                $featured = isset($_POST['featured']) ? 1 : 0;

                // Handle main image upload
                $image_update = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                    $filename = $_FILES['image']['name'];
                    $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    
                    if (in_array($file_ext, $allowed)) {
                        $new_filename = uniqid('package_') . '.' . $file_ext;
                        $upload_path = '../images/' . $new_filename;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                            // Delete old image if exists
                            $old_image_query = "SELECT image FROM packages WHERE id = ?";
                            $old_image_stmt = mysqli_prepare($conn, $old_image_query);
                            mysqli_stmt_bind_param($old_image_stmt, "i", $id);
                            mysqli_stmt_execute($old_image_stmt);
                            $old_image_result = mysqli_stmt_get_result($old_image_stmt);
                            $old_image = mysqli_fetch_assoc($old_image_result)['image'];
                            
                            if ($old_image && file_exists('../images/' . $old_image)) {
                                unlink('../images/' . $old_image);
                            }
                            
                            $image_update = ", image = '$new_filename'";
                        }
                    }
                }

                $query = "UPDATE packages SET name = ?, description = ?, price = ?, duration = ?, featured = ? $image_update WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "ssdsii", $name, $description, $price, $duration, $featured, $id);
                
                if (mysqli_stmt_execute($stmt)) {
                    // Handle multiple images upload
                    if (isset($_FILES['additional_images']) && is_array($_FILES['additional_images']['name'])) {
                        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                        // Process each uploaded file
                        $image_query = "INSERT INTO package_images (package_id, image_path) VALUES (?, ?)";
                        $image_stmt = mysqli_prepare($conn, $image_query);
                        
                        for ($i = 0; $i < count($_FILES['additional_images']['name']); $i++) {
                            if ($_FILES['additional_images']['error'][$i] == 0) {
                                $filename = $_FILES['additional_images']['name'][$i];
                                $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                                
                                if (in_array($file_ext, $allowed)) {
                                    $new_filename = uniqid('package_img_') . '.' . $file_ext;
                                    $upload_path = '../images/' . $new_filename;
                                    
                                    if (move_uploaded_file($_FILES['additional_images']['tmp_name'][$i], $upload_path)) {
                                        mysqli_stmt_bind_param($image_stmt, "is", $id, $new_filename);
                                        mysqli_stmt_execute($image_stmt);
                                    }
                                }
                            }
                        }
                    }
                    
                    // Update package destinations
                    // First, remove all existing destinations
                    $delete_dest_query = "DELETE FROM package_destinations WHERE package_id = ?";
                    $delete_dest_stmt = mysqli_prepare($conn, $delete_dest_query);
                    mysqli_stmt_bind_param($delete_dest_stmt, "i", $id);
                    mysqli_stmt_execute($delete_dest_stmt);
                    
                    // Then add new destinations
                    if (isset($_POST['destinations']) && is_array($_POST['destinations'])) {
                        $dest_query = "INSERT INTO package_destinations (package_id, destination_id) VALUES (?, ?)";
                        $dest_stmt = mysqli_prepare($conn, $dest_query);
                        
                        foreach ($_POST['destinations'] as $destination_id) {
                            mysqli_stmt_bind_param($dest_stmt, "ii", $id, $destination_id);
                            mysqli_stmt_execute($dest_stmt);
                        }
                    }
                    
                    $_SESSION['success_message'] = "Package updated successfully.";
                } else {
                    $_SESSION['error_message'] = "Error updating package: " . mysqli_error($conn);
                }
                break;
                
            case 'delete':
                $id = sanitize_input($_POST['id']);
                
                // Get the image filename before deleting
                $query = "SELECT image FROM packages WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "i", $id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $package = mysqli_fetch_assoc($result);
                
                // Get additional images
                $images_query = "SELECT image_path FROM package_images WHERE package_id = ?";
                $images_stmt = mysqli_prepare($conn, $images_query);
                mysqli_stmt_bind_param($images_stmt, "i", $id);
                mysqli_stmt_execute($images_stmt);
                $images_result = mysqli_stmt_get_result($images_stmt);
                $additional_images = [];
                while ($image_row = mysqli_fetch_assoc($images_result)) {
                    $additional_images[] = $image_row['image_path'];
                }
                
                // Delete the package
                $query = "DELETE FROM packages WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "i", $id);
                
                if (mysqli_stmt_execute($stmt)) {
                    // Delete the main image file if it exists
                    if ($package['image'] && file_exists('../images/' . $package['image'])) {
                        unlink('../images/' . $package['image']);
                    }
                    
                    // Delete all additional image files
                    foreach ($additional_images as $img_path) {
                        if (file_exists('../images/' . $img_path)) {
                            unlink('../images/' . $img_path);
                        }
                    }
                    
                    $_SESSION['success_message'] = "Package deleted successfully.";
                } else {
                    $_SESSION['error_message'] = "Error deleting package: " . mysqli_error($conn);
                }
                break;
                
            case 'delete_image':
                $image_id = sanitize_input($_POST['image_id']);
                
                // Get the image filename before deleting
                $query = "SELECT image_path FROM package_images WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "i", $image_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $image = mysqli_fetch_assoc($result);
                
                // Delete the image record
                $query = "DELETE FROM package_images WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "i", $image_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    // Delete the image file if it exists
                    if ($image['image_path'] && file_exists('../images/' . $image['image_path'])) {
                        unlink('../images/' . $image['image_path']);
                    }
                    
                    $_SESSION['success_message'] = "Image deleted successfully.";
                } else {
                    $_SESSION['error_message'] = "Error deleting image: " . mysqli_error($conn);
                }
                break;
        }
        
        // Redirect to refresh the page
        redirect('packages.php');
    }
}

// Get all destinations for dropdown
$query = "SELECT * FROM destinations ORDER BY name";
$result = mysqli_query($conn, $query);
$destinations = [];
while ($row = mysqli_fetch_assoc($result)) {
    $destinations[] = $row;
}

// Get all packages with image count
$query = "SELECT p.*, (SELECT COUNT(*) FROM package_images WHERE package_id = p.id) as image_count 
          FROM packages p ORDER BY p.created_at DESC";
$result = mysqli_query($conn, $query);
$packages = [];
while ($row = mysqli_fetch_assoc($result)) {
    $packages[] = $row;
}

// Page Title
$page_title = "Manage Packages";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Packages - Tourism Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/sidebar.php'; ?>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h1 class="h2 mb-3">Manage Packages</h1>
                
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php 
                        echo $_SESSION['success_message']; 
                        unset($_SESSION['success_message']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php 
                        echo $_SESSION['error_message']; 
                        unset($_SESSION['error_message']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title">All Packages</h5>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPackageModal">
                                <i class="fas fa-plus"></i> Add New Package
                            </button>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Price</th>
                                        <th>Duration</th>
                                        <th>Images</th>
                                        <th>Featured</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($packages as $package): ?>
                                    <tr>
                                        <td><?php echo $package['id']; ?></td>
                                        <td>
                                            <?php if ($package['image']): ?>
                                                <img src="../images/<?php echo $package['image']; ?>" 
                                                     alt="<?php echo $package['name']; ?>" 
                                                     class="img-thumbnail" style="width: 80px; height: 60px; object-fit: cover;">
                                            <?php else: ?>
                                                <span class="text-muted">No image</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $package['name']; ?></td>
                                        <td>$<?php echo number_format($package['price'], 2); ?></td>
                                        <td><?php echo $package['duration']; ?></td>
                                        <td><span class="badge bg-info"><?php echo $package['image_count']; ?></span></td>
                                        <td>
                                            <?php if ($package['featured']): ?>
                                                <span class="badge bg-success">Yes</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">No</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($package['created_at'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary edit-package" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editPackageModal"
                                                    data-id="<?php echo $package['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($package['name']); ?>"
                                                    data-description="<?php echo htmlspecialchars($package['description']); ?>"
                                                    data-price="<?php echo $package['price']; ?>"
                                                    data-duration="<?php echo htmlspecialchars($package['duration']); ?>"
                                                    data-featured="<?php echo $package['featured']; ?>"
                                                    data-image="<?php echo $package['image']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-package" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deletePackageModal"
                                                    data-id="<?php echo $package['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($package['name']); ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    
                                    <?php if (empty($packages)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center">No packages found</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Package Modal -->
    <div class="modal fade" id="addPackageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Package</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label class="form-label">Package Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price</label>
                            <input type="number" class="form-control" name="price" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Duration</label>
                            <input type="text" class="form-control" name="duration" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Main Thumbnail Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                            <small class="text-muted">This will be the main image displayed in listings. Recommended size: 800x600 pixels</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Additional Images</label>
                            <input type="file" class="form-control" name="additional_images[]" accept="image/*" multiple>
                            <small class="text-muted">You can select multiple images. These will be displayed in the package details gallery.</small>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="featured" id="add_featured">
                                <label class="form-check-label" for="add_featured">Featured Package</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Included Destinations</label>
                            <select class="form-select" name="destinations[]" multiple size="5">
                                <?php foreach ($destinations as $destination): ?>
                                <option value="<?php echo $destination['id']; ?>">
                                    <?php echo $destination['name']; ?> ($<?php echo $destination['price']; ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple destinations</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Package</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Package Modal -->
    <div class="modal fade" id="editPackageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Package</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Package Name</label>
                            <input type="text" class="form-control" name="name" id="edit_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="edit_description" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price</label>
                            <input type="number" class="form-control" name="price" id="edit_price" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Duration</label>
                            <input type="text" class="form-control" name="duration" id="edit_duration" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Main Thumbnail Image</label>
                            <div id="current_image" class="mb-2"></div>
                            <input type="file" class="form-control" name="image" accept="image/*">
                            <small class="text-muted">Leave empty to keep current main image. Recommended size: 800x600 pixels</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Additional Images</label>
                            <div id="additional_images" class="d-flex flex-wrap gap-2 mb-2"></div>
                            <input type="file" class="form-control" name="additional_images[]" accept="image/*" multiple>
                            <small class="text-muted">You can select multiple new images to add to the gallery.</small>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="featured" id="edit_featured">
                                <label class="form-check-label" for="edit_featured">Featured Package</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Included Destinations</label>
                            <select class="form-select" name="destinations[]" id="edit_destinations" multiple size="5">
                                <?php foreach ($destinations as $destination): ?>
                                <option value="<?php echo $destination['id']; ?>">
                                    <?php echo $destination['name']; ?> ($<?php echo $destination['price']; ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple destinations</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Package</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Package Modal -->
    <div class="modal fade" id="deletePackageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Package</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete package <strong id="delete_name"></strong>?</p>
                    <p class="text-danger">This action cannot be undone!</p>
                </div>
                <div class="modal-footer">
                    <form method="POST">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete_id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Package</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Image Modal -->
    <div class="modal fade" id="deleteImageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this image?</p>
                    <p class="text-danger">This action cannot be undone!</p>
                    <div id="delete_image_preview" class="text-center"></div>
                </div>
                <div class="modal-footer">
                    <form method="POST">
                        <input type="hidden" name="action" value="delete_image">
                        <input type="hidden" name="image_id" id="delete_image_id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Image</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script>
    // Handle Edit Package Modal
    document.querySelectorAll('.edit-package').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.dataset.id;
            document.getElementById('edit_name').value = this.dataset.name;
            document.getElementById('edit_description').value = this.dataset.description;
            document.getElementById('edit_price').value = this.dataset.price;
            document.getElementById('edit_duration').value = this.dataset.duration;
            document.getElementById('edit_featured').checked = this.dataset.featured === '1';
            
            // Show current image if exists
            const currentImageDiv = document.getElementById('current_image');
            if (this.dataset.image) {
                currentImageDiv.innerHTML = `
                    <img src="../images/${this.dataset.image}" alt="Current Image" class="img-thumbnail" style="max-width: 200px;">
                    <p class="mt-1">Current Main Image</p>
                `;
            } else {
                currentImageDiv.innerHTML = '<p>No current image</p>';
            }
            
            // Load additional images
            const additionalImagesDiv = document.getElementById('additional_images');
            additionalImagesDiv.innerHTML = '<p>Loading images...</p>';
            
            fetch(`get_package_images.php?package_id=${this.dataset.id}`)
                .then(response => response.json())
                .then(images => {
                    if (images.length === 0) {
                        additionalImagesDiv.innerHTML = '<p>No additional images</p>';
                    } else {
                        additionalImagesDiv.innerHTML = '';
                        images.forEach(image => {
                            const imageContainer = document.createElement('div');
                            imageContainer.className = 'position-relative';
                            imageContainer.innerHTML = `
                                <div class="img-thumbnail" style="width: 150px; height: 120px;">
                                    <img src="../images/${image.image_path}" alt="Package Image" 
                                         style="width: 100%; height: 85px; object-fit: cover;">
                                    <div class="d-flex justify-content-center mt-1">
                                        <button type="button" class="btn btn-sm btn-danger delete-additional-image"
                                                data-bs-toggle="modal" data-bs-target="#deleteImageModal"
                                                data-image-id="${image.id}" 
                                                data-image-path="${image.image_path}">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            `;
                            additionalImagesDiv.appendChild(imageContainer);
                        });
                        
                        // Add event listeners for delete buttons
                        document.querySelectorAll('.delete-additional-image').forEach(btn => {
                            btn.addEventListener('click', function(e) {
                                document.getElementById('delete_image_id').value = this.dataset.imageId;
                                document.getElementById('delete_image_preview').innerHTML = `
                                    <img src="../images/${this.dataset.imagePath}" alt="Image Preview" class="img-thumbnail" style="max-width: 200px;">
                                `;
                            });
                        });
                    }
                })
                .catch(error => {
                    additionalImagesDiv.innerHTML = '<p class="text-danger">Error loading images</p>';
                    console.error('Error loading images:', error);
                });
            
            // Load package destinations
            fetch(`get_package_destinations.php?package_id=${this.dataset.id}`)
                .then(response => response.json())
                .then(destinations => {
                    const select = document.getElementById('edit_destinations');
                    Array.from(select.options).forEach(option => {
                        option.selected = destinations.includes(parseInt(option.value));
                    });
                });
        });
    });

    // Handle Delete Package Modal
    document.querySelectorAll('.delete-package').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('delete_id').value = this.dataset.id;
            document.getElementById('delete_name').textContent = this.dataset.name;
        });
    });
    </script>
</body>
</html>