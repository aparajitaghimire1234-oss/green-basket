<?php
/**
 * Admin Products Management
 */

require_once '../includes/functions.php';
require_once '../includes/admin_auth.php';

$page_title = 'Manage Products';

// Handle product actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize_input($_POST['action'] ?? '');
    
    if ($action === 'add' || $action === 'edit') {
        $category_id = intval($_POST['category_id'] ?? 0);
        $product_name = sanitize_input($_POST['product_name'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $original_price = floatval($_POST['original_price'] ?? 0);
        $stock_quantity = intval($_POST['stock_quantity'] ?? 0);
        $sku = sanitize_input($_POST['sku'] ?? '');
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $errors = [];
        if ($category_id <= 0) $errors[] = 'Category is required';
        if (empty($product_name)) $errors[] = 'Product name is required';
        if (empty($description)) $errors[] = 'Description is required';
        if ($price <= 0) $errors[] = 'Price is required';
        if ($stock_quantity < 0) $errors[] = 'Invalid stock quantity';
        
        if (empty($errors)) {
            // Handle image upload
            $image = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = UPLOADS_PATH . '/products';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                $image = upload_image($_FILES['image'], $upload_dir);
            }
            
            if ($action === 'add') {
                $sql = "INSERT INTO products (category_id, product_name, description, price, original_price, stock_quantity, sku, is_featured, is_active, image) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                prepared_execute($sql, "isddisiiss", [
                    $category_id, $product_name, $description, $price, $original_price, 
                    $stock_quantity, $sku, $is_featured, $is_active, $image
                ]);
                set_flash_message('success', 'Product added successfully');
            } else {
                $product_id = intval($_POST['product_id'] ?? 0);
                if ($image) {
                    $sql = "UPDATE products SET category_id = ?, product_name = ?, description = ?, price = ?, original_price = ?, 
                            stock_quantity = ?, sku = ?, is_featured = ?, is_active = ?, image = ? WHERE product_id = ?";
                    prepared_execute($sql, "isddisiissi", [
                        $category_id, $product_name, $description, $price, $original_price, 
                        $stock_quantity, $sku, $is_featured, $is_active, $image, $product_id
                    ]);
                } else {
                    $sql = "UPDATE products SET category_id = ?, product_name = ?, description = ?, price = ?, original_price = ?, 
                            stock_quantity = ?, sku = ?, is_featured = ?, is_active = ? WHERE product_id = ?";
                    prepared_execute($sql, "isddisiisi", [
                        $category_id, $product_name, $description, $price, $original_price, 
                        $stock_quantity, $sku, $is_featured, $is_active, $product_id
                    ]);
                }
                set_flash_message('success', 'Product updated successfully');
            }
            redirect(SITE_URL . '/admin/products.php');
        }
    }
    
    if ($action === 'delete') {
        $product_id = intval($_POST['product_id'] ?? 0);
        if ($product_id > 0) {
            $sql = "DELETE FROM products WHERE product_id = ?";
            prepared_execute($sql, "i", [$product_id]);
            set_flash_message('success', 'Product deleted successfully');
        }
        redirect(SITE_URL . '/admin/products.php');
    }
}

// Get products with pagination
$page = intval($_GET['page'] ?? 1);
$per_page = ADMIN_PRODUCTS_PER_PAGE;
$offset = ($page - 1) * $per_page;

$count_sql = "SELECT COUNT(*) as total FROM products";
$count_result = prepared_select($count_sql);
$total_row = $count_result->fetch_assoc();
$total_products = $total_row['total'];
$total_pages = ceil($total_products / $per_page);

$sql = "SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
$result = prepared_select($sql, "ii", [$per_page, $offset]);
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

// Get categories
$categories = get_all_categories();
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
                    <a class="nav-link active" href="products.php">
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
                    <h2>Manage Products</h2>
                    <button class="btn btn-success" onclick="openAddProductModal()">
                        <i class="bi bi-plus-circle"></i> Add Product
                    </button>
                </div>
                
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td>
                                                <?php if ($product['image']): ?>
                                                    <img src="<?php echo UPLOADS_URL . '/products/' . htmlspecialchars($product['image']); ?>" 
                                                         alt="" style="width: 50px; height: 50px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                        <i class="bi bi-box-seam" style="color: #ccc;"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($product['product_name']); ?></strong>
                                                <?php if ($product['is_featured']): ?>
                                                    <span class="badge bg-warning ms-1">Featured</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                            <td><?php echo format_price($product['price']); ?></td>
                                            <td>
                                                <span class="badge <?php echo $product['stock_quantity'] < 10 ? 'bg-danger' : 'bg-success'; ?>">
                                                    <?php echo $product['stock_quantity']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $product['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                                    <?php echo $product['is_active'] ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" onclick="editProduct(<?php echo $product['product_id']; ?>)">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteProduct(<?php echo $product['product_id']; ?>)">
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
    
    <!-- Product Modal -->
    <div class="modal fade" id="productModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productModalTitle">Add Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="productForm" method="POST" action="" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" id="productAction" value="add">
                        <input type="hidden" name="product_id" id="productId" value="0">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category *</label>
                                <select class="form-select" name="category_id" id="productCategory" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">SKU</label>
                                <input type="text" class="form-control" name="sku" id="productSku">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Product Name *</label>
                            <input type="text" class="form-control" name="product_name" id="productName" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description *</label>
                            <textarea class="form-control" name="description" id="productDescription" rows="4" required></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Price *</label>
                                <input type="number" class="form-control" name="price" id="productPrice" step="0.01" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Original Price</label>
                                <input type="number" class="form-control" name="original_price" id="productOriginalPrice" step="0.01">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Stock Quantity *</label>
                                <input type="number" class="form-control" name="stock_quantity" id="productStock" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Product Image</label>
                            <div id="currentImagePreview" class="mb-2"></div>
                            <input type="file" class="form-control" name="image" accept="image/*">
                            <small class="text-muted">Recommended size: 500x500px. Leave empty to keep the current image.</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_featured" id="productFeatured">
                                    <label class="form-check-label" for="productFeatured">Featured Product</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="productActive" checked>
                                    <label class="form-check-label" for="productActive">Active</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Embed the current page's product data so we can populate the edit form
        // without needing a separate AJAX request.
        const productsData = <?php echo json_encode($products); ?>;

        function openAddProductModal() {
            const form = document.getElementById('productForm');
            form.reset();
            document.getElementById('productAction').value = 'add';
            document.getElementById('productId').value = 0;
            document.getElementById('productModalTitle').textContent = 'Add Product';
            document.getElementById('currentImagePreview').innerHTML = '';
            document.getElementById('productActive').checked = true;

            const modal = new bootstrap.Modal(document.getElementById('productModal'));
            modal.show();
        }

        function editProduct(productId) {
            const product = productsData.find(p => parseInt(p.product_id) === parseInt(productId));

            if (!product) {
                alert('Could not find this product\'s data. Please refresh the page and try again.');
                return;
            }

            // Populate the form with this product's current values
            document.getElementById('productAction').value = 'edit';
            document.getElementById('productId').value = product.product_id;
            document.getElementById('productCategory').value = product.category_id;
            document.getElementById('productSku').value = product.sku || '';
            document.getElementById('productName').value = product.product_name;
            document.getElementById('productDescription').value = product.description;
            document.getElementById('productPrice').value = product.price;
            document.getElementById('productOriginalPrice').value = product.original_price || '';
            document.getElementById('productStock').value = product.stock_quantity;
            document.getElementById('productFeatured').checked = parseInt(product.is_featured) === 1;
            document.getElementById('productActive').checked = parseInt(product.is_active) === 1;

            // Show the current image, since file inputs can't be pre-filled
            const preview = document.getElementById('currentImagePreview');
            if (product.image) {
                preview.innerHTML = '<img src="<?php echo UPLOADS_URL; ?>/products/' + product.image +
                    '" style="width:80px;height:80px;object-fit:cover;border-radius:6px;" alt="Current image"> ' +
                    '<small class="text-muted d-block">Current image shown above</small>';
            } else {
                preview.innerHTML = '<small class="text-muted">No image currently set</small>';
            }

            document.getElementById('productModalTitle').textContent = 'Edit Product';

            const modal = new bootstrap.Modal(document.getElementById('productModal'));
            modal.show();
        }
        
        function deleteProduct(productId) {
            if (confirm('Are you sure you want to delete this product?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete';
                
                const productIdInput = document.createElement('input');
                productIdInput.type = 'hidden';
                productIdInput.name = 'product_id';
                productIdInput.value = productId;
                
                form.appendChild(actionInput);
                form.appendChild(productIdInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>