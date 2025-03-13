<?php
require_once '../includes/config.php';

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    $_SESSION['error_message'] = 'You do not have permission to access the admin area.';
    redirect('../login.php');
}

// Handle package actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        switch ($action) {
            case 'add':
                $name = sanitize_input($_POST['name']);
                $description = sanitize_input($_POST['description']);
                $price = (float)sanitize_input($_POST['price']);
                $duration = sanitize_input($_POST['duration']);
                $featured = isset($_POST['featured']) ? 1 : 0;
                
                // Handle image upload
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
                mysqli_stmt_bind_param($stmt, "ssdsss", $name, $description, $price, $duration, $featured, $image);
                
                if (mysqli_stmt_execute($stmt)) {
                    $package_id = mysqli_insert_id($conn);
                    
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

                // Handle image upload
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
                
                // Get image filename before deleting package
                $image_query = "SELECT image FROM packages WHERE id = ?";
                $image_stmt = mysqli_prepare($conn, $image_query);
                mysqli_stmt_bind_param($image_stmt, "i", $id);
                mysqli_stmt_execute($image_stmt);
                $image_result = mysqli_stmt_get_result($image_stmt);
                $image = mysqli_fetch_assoc($image_result)['image'];
                
                $query = "DELETE FROM packages WHERE id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "i", $id);
                
                if (mysqli_stmt_execute($stmt)) {
                    // Delete package image if exists
                    if ($image && file_exists('../images/' . $image)) {
                        unlink('../images/' . $image);
                    }
                    $_SESSION['success_message'] = "Package deleted successfully.";
                } else {
                    $_SESSION['error_message'] = "Error deleting package: " . mysqli_error($conn);
                }
                break;
        }
        
        redirect('packages.php');
    }
}

// Get packages list with search and pagination
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

$where_clause = $search ? "WHERE name LIKE '%$search%' OR description LIKE '%$search%'" : "";
$count_query = "SELECT COUNT(*) as total FROM packages $where_clause";
$count_result = mysqli_query($conn, $count_query);
$total_items = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_items / $items_per_page);

$query = "SELECT * FROM packages $where_clause ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $items_per_page, $offset);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Get all destinations for the forms
$destinations_query = "SELECT * FROM destinations ORDER BY name";
$destinations_result = mysqli_query($conn, $destinations_query);
$destinations = [];
while ($destination = mysqli_fetch_assoc($destinations_result)) {
    $destinations[] = $destination;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Package Management - Tourism Management System</title>
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
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Package Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPackageModal">
                            <i class="fas fa-plus me-2"></i>Add New Package
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
                                <input type="text" class="form-control" name="search" placeholder="Search packages..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary">Search</button>
                                <?php if ($search): ?>
                                    <a href="packages.php" class="btn btn-secondary">Clear</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Packages Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Duration</th>
                                        <th>Price</th>
                                        <th>Featured</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($package = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?php echo $package['id']; ?></td>
                                        <td>
                                            <?php if ($package['image']): ?>
                                                <img src="../images/<?php echo $package['image']; ?>" alt="<?php echo htmlspecialchars($package['name']); ?>" class="img-thumbnail" style="max-width: 50px;">
                                            <?php else: ?>
                                                <img src="../images/placeholder.jpg" alt="No Image" class="img-thumbnail" style="max-width: 50px;">
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($package['name']); ?></td>
                                        <td><?php echo htmlspecialchars($package['duration']); ?></td>
                                        <td><?php echo number_format($package['price'], 2); ?></td>
                                        <td>
                                            <span class="badge <?php echo $package['featured'] ? 'bg-success' : 'bg-secondary'; ?>">
                                                <?php echo $package['featured'] ? 'Yes' : 'No'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($package['created_at'])); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-warning edit-package" 
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
                                            <button type="button" class="btn btn-sm btn-danger delete-package"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deletePackageModal"
                                                    data-id="<?php echo $package['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($package['name']); ?>">
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
                            <input type="text" class="form-control" name="duration" placeholder="e.g., 3 days" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Package Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                            <small class="text-muted">Recommended size: 800x600 pixels</small>
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
                                    <?php echo htmlspecialchars($destination['name']); ?> - <?php echo htmlspecialchars($destination['location']); ?>
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
                            <label class="form-label">Package Image</label>
                            <div id="current_image" class="mb-2"></div>
                            <input type="file" class="form-control" name="image" accept="image/*">
                            <small class="text-muted">Leave empty to keep current image. Recommended size: 800x600 pixels</small>
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
                                    <?php echo htmlspecialchars($destination['name']); ?> - <?php echo htmlspecialchars($destination['location']); ?>
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
                    <p class="mt-1">Current Image</p>
                `;
            } else {
                currentImageDiv.innerHTML = '<p>No current image</p>';
            }
            
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