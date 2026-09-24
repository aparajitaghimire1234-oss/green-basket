<?php
/**
 * Admin Categories Management
 */

require_once '../includes/functions.php';
require_once '../includes/admin_auth.php';

$page_title = 'Manage Categories';

// Handle category actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize_input($_POST['action'] ?? '');
    
    if ($action === 'add' || $action === 'edit') {
        $category_name = sanitize_input($_POST['category_name'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        
        $errors = [];
        if (empty($category_name)) $errors[] = 'Category name is required';
        
        if (empty($errors)) {
            if ($action === 'add') {
                $sql = "INSERT INTO categories (category_name, description) VALUES (?, ?)";
                prepared_execute($sql, "ss", [$category_name, $description]);
                set_flash_message('success', 'Category added successfully');
            } else {
                $category_id = intval($_POST['category_id'] ?? 0);
                $sql = "UPDATE categories SET category_name = ?, description = ? WHERE category_id = ?";
                prepared_execute($sql, "ssi", [$category_name, $description, $category_id]);
                set_flash_message('success', 'Category updated successfully');
            }
            redirect(SITE_URL . '/admin/categories.php');
        }
    }
    
    if ($action === 'delete') {
        $category_id = intval($_POST['category_id'] ?? 0);
        if ($category_id > 0) {
            // Check if category has products
            $check_sql = "SELECT COUNT(*) as count FROM products WHERE category_id = ?";
            $check_result = prepared_select($check_sql, "i", [$category_id]);
            $check_row = $check_result->fetch_assoc();
            
            if ($check_row['count'] > 0) {
                set_flash_message('danger', 'Cannot delete category with existing products');
            } else {
                $sql = "DELETE FROM categories WHERE category_id = ?";
                prepared_execute($sql, "i", [$category_id]);
                set_flash_message('success', 'Category deleted successfully');
            }
        }
        redirect(SITE_URL . '/admin/categories.php');
    }
}

// Get categories
$sql = "SELECT * FROM categories ORDER BY category_name";
$result = prepared_select($sql);
$categories = [];
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
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
            color: #cccccc !important;
            padding: 15px 20px;
            border-left: 4px solid transparent;
        }
        .sidebar .nav-link i {
            color: #cccccc !important;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: #28a745 !important;
            color: #ffffff !important;
            border-left: 4px solid #ffffff;
            font-weight: 600;
        }
        .sidebar .nav-link:hover i,
        .sidebar .nav-link.active i {
            color: #ffffff !important;
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
                    <a class="nav-link active" href="categories.php">
                        <i class="bi bi-tags me-2"></i> Categories
                    </a>
                    <a class="nav-link" href="orders.php">
                        <i class="bi bi-bag me-2"></i> Orders
                    </a>
                    <a class="nav-link" href="customers.php">
                        <i class="bi bi-people me-2"></i> Customers
                    </a>
                    <a class="nav-link" href="reviews.php">
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
                    <h2>Manage Categories</h2>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#categoryModal">
                        <i class="bi bi-plus-circle"></i> Add Category
                    </button>
                </div>
                
                <div class="row">
                    <?php foreach ($categories as $category): ?>
                        <?php
                        // Get product count for this category
                        $count_sql = "SELECT COUNT(*) as count FROM products WHERE category_id = ?";
                        $count_result = prepared_select($count_sql, "i", [$category['category_id']]);
                        $count_row = $count_result->fetch_assoc();
                        $product_count = $count_row['count'];
                        ?>
                        <div class="col-md-4 mb-4">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="bg-success text-white rounded d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-size: 24px;">
                                            <i class="bi bi-tag"></i>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a class="dropdown-item" href="#" onclick="editCategory(<?php echo $category['category_id']; ?>, '<?php echo htmlspecialchars($category['category_name']); ?>', '<?php echo htmlspecialchars($category['description']); ?>')">Edit</a></li>
                                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteCategory(<?php echo $category['category_id']; ?>)">Delete</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <h5><?php echo htmlspecialchars($category['category_name']); ?></h5>
                                    <p class="text-muted small mb-2"><?php echo htmlspecialchars($category['description']); ?></p>
                                    <span class="badge bg-info"><?php echo $product_count; ?> Products</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Category Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="categoryModalTitle">Add Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="categoryForm" method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="category_id" id="categoryId" value="0">
                        
                        <div class="mb-3">
                            <label class="form-label">Category Name *</label>
                            <input type="text" class="form-control" name="category_name" id="categoryName" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="categoryDescription" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Save Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editCategory(categoryId, categoryName, description) {
            document.getElementById('categoryModalTitle').textContent = 'Edit Category';
            document.getElementById('categoryId').value = categoryId;
            document.getElementById('categoryName').value = categoryName;
            document.getElementById('categoryDescription').value = description;
            document.querySelector('#categoryForm input[name="action"]').value = 'edit';
            
            const modal = new bootstrap.Modal(document.getElementById('categoryModal'));
            modal.show();
        }
        
        function deleteCategory(categoryId) {
            if (confirm('Are you sure you want to delete this category?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete';
                
                const categoryIdInput = document.createElement('input');
                categoryIdInput.type = 'hidden';
                categoryIdInput.name = 'category_id';
                categoryIdInput.value = categoryId;
                
                form.appendChild(actionInput);
                form.appendChild(categoryIdInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Reset modal when closed
        document.getElementById('categoryModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('categoryModalTitle').textContent = 'Add Category';
            document.getElementById('categoryId').value = '0';
            document.getElementById('categoryName').value = '';
            document.getElementById('categoryDescription').value = '';
            document.querySelector('#categoryForm input[name="action"]').value = 'add';
        });
    </script>
</body>
</html>
