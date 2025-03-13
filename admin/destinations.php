<?php
require_once '../includes/config.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    $_SESSION['error_message'] = 'You do not have permission to access the admin area.';
    redirect('../login.php');
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
                $price = sanitize_input($_POST['price']);
                $featured = isset($_POST['featured']) ? 1 : 0;

                // Handle image upload
                $image = '';
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                    $filename = $_FILES['image']['name'];
                    $filetype = pathinfo($filename, PATHINFO_EXTENSION);
                    
                    if (in_array(strtolower($filetype), $allowed)) {
                        $new_filename = uniqid('destination_') . '.' . $filetype;
                        $upload_path = '../images/' . $new_filename;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                            $image = $new_filename;
                        }
                    }
                }

                $query = "INSERT INTO destinations (name, description, location, image, price, featured) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "ssssdi", $name, $description, $location, $image, $price, $featured);
                
                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['success_message'] = "Destination added successfully.";
                } else {
                    $_SESSION['error_message'] = "Error adding destination: " . mysqli_error($conn);
                }
                break;

            case 'edit':
                $id = sanitize_input($_POST['id']);
                $name = sanitize_input($_POST['name']);
                $description = sanitize_input($_POST['description']);
                $location = sanitize_input($_POST['location']);
                $price = sanitize_input($_POST['price']);
                $featured = isset($_POST['featured']) ? 1 : 0;

                // Handle image upload
                if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
                    $filename = $_FILES['image']['name'];
                    $filetype = pathinfo($filename, PATHINFO_EXTENSION);
                    
                    if (in_array(strtolower($filetype), $allowed)) {
                        $new_filename = uniqid('destination_') . '.' . $filetype;
                        $upload_path = '../images/' . $new_filename;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                            // Delete old image
                            $old_image_query = "SELECT image FROM destinations WHERE id = ?";
                            $old_image_stmt = mysqli_prepare($conn, $old_image_query);
                            mysqli_stmt_bind_param($old_image_stmt, "i", $id);
                            mysqli_stmt_execute($old_image_stmt);
                            $old_image_result = mysqli_stmt_get_result($old_image_stmt);
                            $old_image = mysqli_fetch_assoc($old_image_result)['image'];
                            
                            if ($old_image && file_exists('../images/' . $old_image)) {
                                unlink('../images/' . $old_image);
                            }
                            
                            // Update with new image
                            $query = "UPDATE destinations SET name = ?, description = ?, location = ?, image = ?, price = ?, featured = ? WHERE id = ?";
                            $stmt = mysqli_prepare($conn, $query);
                            mysqli_stmt_bind_param($stmt, "ssssdii", $name, $description, $location, $new_filename, $price, $featured, $id);
                        }
                    }
                } else {
                    // Update without changing image
                    $query = "UPDATE destinations SET name = ?, description = ?, location = ?, price = ?, featured = ? WHERE id = ?";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "sssdii", $name, $description, $location, $price, $featured, $id);
                }
                
                if (mysqli_stmt_execute($stmt)) {
                    $_SESSION['success_message'] = "Destination updated successfully.";
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
        }
        
        redirect('destinations.php');
    }
}

// Get destinations list with search and pagination
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

$where_clause = $search ? "WHERE name LIKE '%$search%' OR location LIKE '%$search%'" : "";
$count_query = "SELECT COUNT(*) as total FROM destinations $where_clause";
$count_result = mysqli_query($conn, $count_query);
$total_items = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_items / $items_per_page);

$query = "SELECT * FROM destinations $where_clause ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $items_per_page, $offset);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
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
                                <i class="fas fa-question-circle me-2"></i>
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
                                    <?php while ($destination = mysqli_fetch_assoc($result)): ?>
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
                                    <?php endwhile; ?>
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
                            <img id="edit_current_image" src="" alt="" class="img-thumbnail d-block mb-2" style="max-height: 100px;">
                            <input type="file" class="form-control" name="image" accept="image/*">
                            <small class="text-muted">Leave empty to keep current image</small>
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
            document.getElementById('edit_featured').checked = this.dataset.featured == '1';
            
            const currentImage = document.getElementById('edit_current_image');
            if (this.dataset.image) {
                currentImage.src = '../images/' + this.dataset.image;
                currentImage.style.display = 'block';
            } else {
                currentImage.style.display = 'none';
            }
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