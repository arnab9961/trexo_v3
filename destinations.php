<?php
require_once 'includes/header.php';

// Pagination setup
$limit = 6; // Items per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Get total number of destinations
$total_query = "SELECT COUNT(*) as total FROM destinations";
$total_result = mysqli_query($conn, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_destinations = $total_row['total'];
$total_pages = ceil($total_destinations / $limit);

// Get destinations with pagination
$destinations_query = "SELECT * FROM destinations ORDER BY name LIMIT $offset, $limit";
$destinations_result = mysqli_query($conn, $destinations_query);
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <h1>Explore Destinations</h1>
        <p>Discover amazing places around the world and plan your next adventure.</p>
    </div>
</section>

<!-- Destinations List -->
<section class="mb-5">
    <div class="container">
        <div class="row">
            <?php
            if (mysqli_num_rows($destinations_result) > 0) {
                $image_count = 1;
                while ($destination = mysqli_fetch_assoc($destinations_result)) {
                    // Use one of the 6 available images
                    $image_file = "destination" . $image_count . ".jpg";
                    $image_count = ($image_count % 6) + 1;
            ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <?php if ($destination['featured']): ?>
                            <div class="featured-badge">Featured</div>
                        <?php endif; ?>
                        <img src="images/<?php echo $image_file; ?>" class="card-img-top" alt="<?php echo $destination['name']; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $destination['name']; ?></h5>
                            <p class="card-text"><?php echo substr($destination['description'], 0, 100) . '...'; ?></p>
                            <p><i class="fas fa-map-marker-alt me-2"></i><?php echo $destination['location']; ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="price-tag">৳<?php echo number_format($destination['price']); ?></span>
                                <a href="destination_details.php?id=<?php echo $destination['id']; ?>" class="btn btn-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
                }
            } else {
                echo '<div class="col-12"><p class="text-center">No destinations available.</p></div>';
            }
            ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <nav aria-label="Destinations pagination">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</section>

<?php
require_once 'includes/footer.php';
?> 