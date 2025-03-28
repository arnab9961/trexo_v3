<?php
require_once '../includes/config.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    $_SESSION['error_message'] = 'You do not have permission to access the admin area.';
    redirect('../login.php');
}

// Initialize pagination variables
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Initialize search variable
$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_condition = '';
if (!empty($search)) {
    $search = mysqli_real_escape_string($conn, $search);
    $search_condition = " WHERE name LIKE '%$search%' OR location LIKE '%$search%' OR description LIKE '%$search%'";
}

// Handle destination actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        switch ($action) {
            case 'add':
                $name = sanitize_input($_POST['name']);
                $description = sanitize_input($_POST['description']);
                $location = sanitize_input($_POST['location']);
                $price = (float)sanitize_input($_POST['price']);
                $featured = isset($_POST['featured']) ? 1 : 0;
                
                // Handle main thumbnail image upload
                $image = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                    $filename = $_FILES['image']['name'];
                    $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    
                    if (in_array($file_ext, $allowed)) {
                        $new_filename = uniqid('destination_') . '.' . $file_ext;
                        $upload_path = '../images/' . $new_filename;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                            $image = $new_filename;
                        }
                    }
                }
                
                $query = "INSERT INTO destinations (name, description, location, price, featured, image) 
                         VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "sssdis", $name, $description, $location, $price, $featured, $image);
                
                if (mysqli_stmt_execute($stmt)) {
                    $destination_id = mysqli_insert_id($conn);
                    
                    // Handle multiple images upload
                    if (isset($_FILES['additional_images']) && is_array($_FILES['additional_images']['name'])) {
                        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                        $image_query = "INSERT INTO destination_images (destination_id, image_path) VALUES (?, ?)";
                        $image_stmt = mysqli_prepare($conn, $image_query);
                        
                        for ($i = 0; $i < count($_FILES['additional_images']['name']); $i++) {
                            if ($_FILES['additional_images']['error'][$i] == 0) {
                                $filename = $_FILES['additional_images']['name'][$i];
                                $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                                
                                if (in_array($file_ext, $allowed)) {
                                    $new_filename = uniqid('dest_img_') . '.' . $file_ext;
                                    $upload_path = '../images/' . $new_filename;
                                    
                                    if (move_uploaded_file($_FILES['additional_images']['tmp_name'][$i], $upload_path)) {
                                        mysqli_stmt_bind_param($image_stmt, "is", $destination_id, $new_filename);
                                        mysqli_stmt_execute($image_stmt);
                                    }
                                }
                            }
                        }
                    }
                    
                    $_SESSION['success_message'] = "Destination added successfully!";
                } else {
                    $_SESSION['error_message'] = "Error adding destination: " . mysqli_error($conn);
                }
                break;

            case 'edit':
                $id = sanitize_input($_POST['id']);
                $name = sanitize_input($_POST['name']);
                $description = sanitize_input($_POST['description']);
                $location = sanitize_input($_POST['location']);
                $price = (float)sanitize_input($_POST['price']);
                $featured = isset($_POST['featured']) ? 1 : 0;
                
                // Handle main image upload if provided
                $image_update = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                    $filename = $_FILES['image']['name'];
                    $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    
                    if (in_array($file_ext, $allowed)) {
                        $new_filename = uniqid('destination_') . '.' . $file_ext;
                        $upload_path = '../images/' . $new_filename;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                            // Delete old image if exists
                            $old_image_query = "SELECT image FROM destinations WHERE id = ?";
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
                
                $query = "UPDATE destinations SET name = ?, description = ?, location = ?, price = ?, featured = ? $image_update WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "sssdii", $name, $description, $location, $price, $featured, $id);
                
                if (mysqli_stmt_execute($stmt)) {
                    // Handle multiple images upload
                    if (isset($_FILES['additional_images']) && is_array($_FILES['additional_images']['name'])) {
                        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                        // Process each uploaded file
                        $image_query = "INSERT INTO destination_images (destination_id, image_path) VALUES (?, ?)";
                        $image_stmt = mysqli_prepare($conn, $image_query);
                        
                        for ($i = 0; $i < count($_FILES['additional_images']['name']); $i++) {
                            if ($_FILES['additional_images']['error'][$i] == 0) {
                                $filename = $_FILES['additional_images']['name'][$i];
                                $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                                
                                if (in_array($file_ext, $allowed)) {
                                    $new_filename = uniqid('dest_img_') . '.' . $file_ext;
                                    $upload_path = '../images/' . $new_filename;
                                    
                                    if (move_uploaded_file($_FILES['additional_images']['tmp_name'][$i], $upload_path)) {
                                        mysqli_stmt_bind_param($image_stmt, "is", $id, $new_filename);
                                        mysqli_stmt_execute($image_stmt);
                                    }
                                }
                            }
                        }
                    }
                    
                    $_SESSION['success_message'] = "Destination updated successfully!";
                } else {
                    $_SESSION['error_message'] = "Error updating destination: " . mysqli_error($conn);
                }
                break;

            case 'delete':
                $id = sanitize_input($_POST['id']);
                
                // Delete image file first
                $image_query = "SELECT image FROM destinations WHERE id = ?";
                $image_stmt = mysqli_prepare($conn, $image_query);
                mysqli_stmt_bind_param($image_stmt, "i", $id);
                mysqli_stmt_execute($image_stmt);
                $image_result = mysqli_stmt_get_result($image_stmt);
                $image = mysqli_fetch_assoc($image_result)['image'];
                
                if ($image && file_exists('../images/' . $image)) {
                    unlink('../images/' . $image);
                }
                
                // Delete destination record
                $query = "DELETE FROM destinations WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "i", $id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['success_message'] = "Destination deleted successfully.";
                } else {
                    $_SESSION['error_message'] = "Error deleting destination: " . mysqli_error($conn);
                }
                break;

            case 'delete_image':
                $image_id = sanitize_input($_POST['image_id']);
                
                // Get the image filename before deleting
                $query = "SELECT image_path FROM destination_images WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "i", $image_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $image = mysqli_fetch_assoc($result);
                
                // Delete the image record
                $query = "DELETE FROM destination_images WHERE id = ?";
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
        
        redirect('destinations.php');
    }
}

// Get all destinations with image count
$count_query = "SELECT COUNT(*) as total FROM destinations" . $search_condition;
$count_result = mysqli_query($conn, $count_query);
$total_records = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_records / $records_per_page);

$query = "SELECT d.*, (SELECT COUNT(*) FROM destination_images WHERE destination_id = d.id) as image_count 
          FROM destinations d" . $search_condition . " ORDER BY d.created_at DESC LIMIT $offset, $records_per_page";
$result = mysqli_query($conn, $query);
$destinations = [];
while ($row = mysqli_fetch_assoc($result)) {
    $destinations[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Destination Management - Tourism Management System</title>
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
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="index.php">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="users.php">
                                <i class="fas fa-users me-2"></i>
                                Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active text-white" href="destinations.php">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                Destinations
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="packages.php">
                                <i class="fas fa-box me-2"></i>
                                Packages
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="bookings.php">
                                <i class="fas fa-calendar-check me-2"></i>
                                Bookings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="inquiries.php">
                                <i class="fas fa-calendar-check me-2"></i>
                                Inquiries
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link text-white" href="reviews.php">
                                <i class="fas fa-star me-2"></i>
                                Reviews
                            </a>
                        </li>
                        <li class="nav-item mt-5">
                            <a class="nav-link text-white" href="../index.php">
                                <i class="fas fa-home me-2"></i>
                                Back to Website
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="../logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Destination Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDestinationModal">
                            <i class="fas fa-plus me-2"></i>Add New Destination
                        </button>
                    </div>
                </div>

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

                <!-- Search Form -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-8">
                                <input type="text" class="form-control" name="search" placeholder="Search destinations..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary">Search</button>
                                <?php if ($search): ?>
                                    <a href="destinations.php" class="btn btn-secondary">Clear</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Destinations Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Location</th>
                                        <th>Price</th>
                                        <th>Featured</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($destinations as $destination): ?>
                                    <tr>
                                        <td><?php echo $destination['id']; ?></td>
                                        <td>
                                            <?php if ($destination['image']): ?>
                                                <img src="../images/<?php echo $destination['image']; ?>" 
                                                     alt="<?php echo htmlspecialchars($destination['name']); ?>"
                                                     class="img-thumbnail"
                                                     style="max-width: 50px;">
                                            <?php else: ?>
                                                <span class="text-muted">No image</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($destination['name']); ?></td>
                                        <td><?php echo htmlspecialchars($destination['location']); ?></td>
                                        <td><?php echo number_format($destination['price'], 2); ?></td>
                                        <td>
                                            <span class="badge <?php echo $destination['featured'] ? 'bg-success' : 'bg-secondary'; ?>">
                                                <?php echo $destination['featured'] ? 'Yes' : 'No'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($destination['created_at'])); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info view-destination" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#viewDestinationModal"
                                                    data-id="<?php echo $destination['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($destination['name']); ?>"
                                                    data-description="<?php echo htmlspecialchars($destination['description']); ?>"
                                                    data-location="<?php echo htmlspecialchars($destination['location']); ?>"
                                                    data-price="<?php echo $destination['price']; ?>"
                                                    data-image="<?php echo $destination['image']; ?>"
                                                    data-featured="<?php echo $destination['featured']; ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-warning edit-destination" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editDestinationModal"
                                                    data-id="<?php echo $destination['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($destination['name']); ?>"
                                                    data-description="<?php echo htmlspecialchars($destination['description']); ?>"
                                                    data-location="<?php echo htmlspecialchars($destination['location']); ?>"
                                                    data-price="<?php echo $destination['price']; ?>"
                                                    data-image="<?php echo $destination['image']; ?>"
                                                    data-featured="<?php echo $destination['featured']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger delete-destination"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteDestinationModal"
                                                    data-id="<?php echo $destination['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($destination['name']); ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo ($page - 1); ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Previous</a>
                                </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo ($page + 1); ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Next</a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Destination Modal -->
    <div class="modal fade" id="addDestinationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Destination</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-control" name="location" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price</label>
                            <input type="number" class="form-control" name="price" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Additional Images</label>
                            <input type="file" class="form-control" name="additional_images[]" accept="image/*" multiple>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="featured" id="add_featured">
                                <label class="form-check-label" for="add_featured">Featured Destination</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Destination</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Destination Modal -->
    <div class="modal fade" id="viewDestinationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">View Destination</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <img id="view_image" src="" alt="" class="img-fluid mb-3" style="max-height: 200px;">
                    </div>
                    <table class="table">
                        <tr>
                            <th>Name:</th>
                            <td id="view_name"></td>
                        </tr>
                        <tr>
                            <th>Description:</th>
                            <td id="view_description"></td>
                        </tr>
                        <tr>
                            <th>Location:</th>
                            <td id="view_location"></td>
                        </tr>
                        <tr>
                            <th>Price:</th>
                            <td id="view_price"></td>
                        </tr>
                        <tr>
                            <th>Featured:</th>
                            <td id="view_featured"></td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Destination Modal -->
    <div class="modal fade" id="editDestinationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Destination</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" id="edit_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="edit_description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-control" name="location" id="edit_location" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price</label>
                            <input type="number" class="form-control" name="price" id="edit_price" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Current Image</label>
                            <div id="current_image"></div>
                            <input type="file" class="form-control" name="image" accept="image/*">
                            <small class="text-muted">Leave empty to keep current image</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Additional Images</label>
                            <div id="additional_images"></div>
                            <input type="file" class="form-control mt-3" name="additional_images[]" accept="image/*" multiple>
                            <small class="text-muted">You can select multiple new images to add to the gallery.</small>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="featured" id="edit_featured">
                                <label class="form-check-label" for="edit_featured">Featured Destination</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Destination</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Destination Modal -->
    <div class="modal fade" id="deleteDestinationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Destination</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete destination <strong id="delete_name"></strong>?</p>
                    <p class="text-danger">This action cannot be undone!</p>
                </div>
                <div class="modal-footer">
                    <form method="POST">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete_id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Destination</button>
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
    // Handle View Destination Modal
    document.querySelectorAll('.view-destination').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('view_name').textContent = this.dataset.name;
            document.getElementById('view_description').textContent = this.dataset.description;
            document.getElementById('view_location').textContent = this.dataset.location;
            document.getElementById('view_price').textContent = parseFloat(this.dataset.price).toFixed(2);
            document.getElementById('view_featured').textContent = this.dataset.featured == '1' ? 'Yes' : 'No';
            
            const image = document.getElementById('view_image');
            if (this.dataset.image) {
                image.src = '../images/' + this.dataset.image;
                image.style.display = 'block';
            } else {
                image.style.display = 'none';
            }
        });
    });

    // Handle Edit Destination Modal
    document.querySelectorAll('.edit-destination').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('edit_id').value = this.dataset.id;
            document.getElementById('edit_name').value = this.dataset.name;
            document.getElementById('edit_description').value = this.dataset.description;
            document.getElementById('edit_location').value = this.dataset.location;
            document.getElementById('edit_price').value = this.dataset.price;
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
            
            fetch(`get_destination_images.php?destination_id=${this.dataset.id}`)
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
                                    <img src="../images/${image.image_path}" alt="Destination Image" 
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
        });
    });

    // Handle Delete Destination Modal
    document.querySelectorAll('.delete-destination').forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('delete_id').value = this.dataset.id;
            document.getElementById('delete_name').textContent = this.dataset.name;
        });
    });
    </script>
</body>
</html>