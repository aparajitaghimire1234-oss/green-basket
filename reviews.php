<?php
/**
 * Admin Reviews Management
 */

require_once '../includes/functions.php';
require_once '../includes/admin_auth.php';

$page_title = 'Manage Reviews';

// Handle review deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $review_id = intval($_POST['review_id'] ?? 0);
    if ($review_id > 0) {
        $sql = "DELETE FROM reviews WHERE review_id = ?";
        prepared_execute($sql, "i", [$review_id]);
        set_flash_message('success', 'Review deleted successfully');
    }
    redirect(SITE_URL . '/admin/reviews.php');
}

// Get reviews with pagination
$page = intval($_GET['page'] ?? 1);
$per_page = ADMIN_PRODUCTS_PER_PAGE;
$offset = ($page - 1) * $per_page;

$count_sql = "SELECT COUNT(*) as total FROM reviews";
$count_result = prepared_select($count_sql);
$total_row = $count_result->fetch_assoc();
$total_reviews = $total_row['total'];
$total_pages = ceil($total_reviews / $per_page);

$sql = "SELECT r.*, p.product_name, u.first_name, u.last_name 
        FROM reviews r 
        LEFT JOIN products p ON r.product_id = p.product_id 
        LEFT JOIN users u ON r.user_id = u.user_id 
        ORDER BY r.created_at DESC 
        LIMIT ? OFFSET ?";
$result = prepared_select($sql, "ii", [$per_page, $offset]);
$reviews = [];
while ($row = $result->fetch_assoc()) {
    $reviews[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - GreenBasket Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo ASSETS_PATH; ?>/css/style.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #1a1a1a;
            color: white;
        }
        .sidebar .nav-link {
            color: #ccc;
            padding: 15px 20px;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: #28a745;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-0">
                <div class="p-3 text-center border-bottom border-secondary">
                    <i class="bi bi-basket-fill" style="font-size: 30px; color: #28a745;"></i>
                    <h5 class="mt-2">GreenBasket</h5>
                    <small>Admin Panel</small>
                </div>
                <nav class="nav flex-column mt-3">
                    <a class="nav-link" href="index.php">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                    <a class="nav-link" href="products.php">
                        <i class="bi bi-box-seam me-2"></i> Products
                    </a>
                    <a class="nav-link" href="categories.php">
                        <i class="bi bi-tags me-2"></i> Categories
                    </a>
                    <a class="nav-link" href="orders.php">
                        <i class="bi bi-bag me-2"></i> Orders
                    </a>
                    <a class="nav-link" href="customers.php">
                        <i class="bi bi-people me-2"></i> Customers
                    </a>
                    <a class="nav-link active" href="reviews.php">
                        <i class="bi bi-star me-2"></i> Reviews
                    </a>
                    <a class="nav-link" href="coupons.php">
                        <i class="bi bi-ticket-perforated me-2"></i> Coupons
                    </a>
                    <a class="nav-link" href="reports.php">
                        <i class="bi bi-graph-up me-2"></i> Reports
                    </a>
                    <hr class="border-secondary">
                    <a class="nav-link" href="../index.php" target="_blank">
                        <i class="bi bi-eye me-2"></i> View Site
                    </a>
                    <a class="nav-link text-danger" href="../logout.php">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <?php
                $flash = get_flash_message();
                if ($flash):
                ?>
                    <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($flash['message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Manage Reviews</h2>
                </div>
                
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Customer</th>
                                        <th>Rating</th>
                                        <th>Review</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reviews as $review): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($review['product_name']); ?></td>
                                            <td><?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></td>
                                            <td>
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="bi bi-star<?php echo $i <= $review['rating'] ? '-fill text-warning' : ''; ?>"></i>
                                                <?php endfor; ?>
                                            </td>
                                            <td><?php echo truncate_text(htmlspecialchars($review['review_text']), 50); ?></td>
                                            <td><?php echo format_date($review['created_at']); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteReview(<?php echo $review['review_id']; ?>)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                                        </li>
                                    <?php endif; ?>
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteReview(reviewId) {
            if (confirm('Are you sure you want to delete this review?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete';
                
                const reviewIdInput = document.createElement('input');
                reviewIdInput.type = 'hidden';
                reviewIdInput.name = 'review_id';
                reviewIdInput.value = reviewId;
                
                form.appendChild(actionInput);
                form.appendChild(reviewIdInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
