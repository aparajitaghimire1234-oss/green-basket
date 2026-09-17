<?php
/**
 * Shop Page
 * Product listing with search, filter, and sorting
 */

require_once 'includes/functions.php';

$page_title = 'Shop';
require_once 'includes/header.php';

// Get parameters
$search = sanitize_input($_GET['search'] ?? '');
$category_id = intval($_GET['category'] ?? 0);
$sort = sanitize_input($_GET['sort'] ?? 'newest');
$page = intval($_GET['page'] ?? 1);
$per_page = PRODUCTS_PER_PAGE;
$offset = ($page - 1) * $per_page;

// Build query
$where_conditions = ["p.is_active = 1"];
$params = [];
$types = "";

if (!empty($search)) {
    $where_conditions[] = "(p.product_name LIKE ? OR p.description LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "ss";
}

if ($category_id > 0) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_id;
    $types .= "i";
}

$where_clause = implode(" AND ", $where_conditions);

// Count total products
$count_sql = "SELECT COUNT(*) as total FROM products p WHERE $where_clause";
$count_result = prepared_select($count_sql, $types, $params);
$total_row = $count_result->fetch_assoc();
$total_products = $total_row['total'];
$total_pages = ceil($total_products / $per_page);

// Sorting
$order_by = "ORDER BY p.created_at DESC";
switch ($sort) {
    case 'price_low':
        $order_by = "ORDER BY p.price ASC";
        break;
    case 'price_high':
        $order_by = "ORDER BY p.price DESC";
        break;
    case 'name':
        $order_by = "ORDER BY p.product_name ASC";
        break;
    case 'popular':
        $order_by = "ORDER BY p.is_featured DESC, p.created_at DESC";
        break;
}

// Get products
$sql = "SELECT p.*, c.category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        WHERE $where_clause 
        $order_by 
        LIMIT ? OFFSET ?";

$params[] = $per_page;
$params[] = $offset;
$types .= "ii";

$result = prepared_select($sql, $types, $params);
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

// Get categories for filter
$categories = get_all_categories();
?>

<!-- Page Header -->
<section class="bg-success text-white py-4">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="fw-bold">Shop</h1>
                <p class="mb-0">Discover our eco-friendly products</p>
            </div>
        </div>
    </div>
</section>

<!-- Shop Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Sidebar Filters -->
            <div class="col-lg-3 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="mb-4">Filters</h5>
                        
                        <!-- Search Form -->
                        <form method="GET" action="" class="mb-4">
                            <div class="mb-3">
                                <label class="form-label">Search</label>
                                <input type="text" name="search" class="form-control" 
                                       value="<?php echo htmlspecialchars($search); ?>" 
                                       placeholder="Search products...">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select name="category" class="form-select">
                                    <option value="0">All Categories</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['category_id']; ?>" 
                                                <?php echo $category_id == $cat['category_id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['category_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Sort By</label>
                                <select name="sort" class="form-select">
                                    <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                    <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                    <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                    <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Name: A to Z</option>
                                    <option value="popular" <?php echo $sort == 'popular' ? 'selected' : ''; ?>>Popular</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-success w-100">Apply Filters</button>
                            <a href="<?php echo SITE_URL; ?>/shop.php" class="btn btn-outline-secondary w-100 mt-2">Clear Filters</a>
                        </form>
                        
                        <!-- Category Links -->
                        <hr>
                        <h6 class="mb-3">Browse Categories</h6>
                        <div class="list-group list-group-flush">
                            <a href="<?php echo SITE_URL; ?>/shop.php" class="list-group-item list-group-item-action <?php echo $category_id == 0 ? 'active' : ''; ?>">
                                All Products
                            </a>
                            <?php foreach ($categories as $cat): ?>
                                <a href="<?php echo SITE_URL; ?>/shop.php?category=<?php echo $cat['category_id']; ?>" 
                                   class="list-group-item list-group-item-action <?php echo $category_id == $cat['category_id'] ? 'active' : ''; ?>">
                                    <?php echo htmlspecialchars($cat['category_name']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Products Grid -->
            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <p class="mb-0">Showing <?php echo count($products); ?> of <?php echo $total_products; ?> products</p>
                    <?php if (!empty($search) || $category_id > 0): ?>
                        <a href="<?php echo SITE_URL; ?>/shop.php" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Clear Filters
                        </a>
                    <?php endif; ?>
                </div>
                
                <?php if (empty($products)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-search" style="font-size: 80px; color: #ccc;"></i>
                        <h4 class="mt-3">No products found</h4>
                        <p class="text-muted">Try adjusting your filters or search terms</p>
                        <a href="<?php echo SITE_URL; ?>/shop.php" class="btn btn-success">View All Products</a>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($products as $product): ?>
                            <?php
                            $rating = get_average_rating($product['product_id']);
                            $discount = 0;
                            if ($product['original_price'] && $product['original_price'] > $product['price']) {
                                $discount = round((($product['original_price'] - $product['price']) / $product['original_price']) * 100);
                            }
                            ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="product-card">
                                    <div class="product-image">
                                        <?php if ($product['image']): ?>
                                            <img src="<?php echo UPLOADS_URL . '/products/' . htmlspecialchars($product['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                                        <?php else: ?>
                                            <i class="bi bi-box-seam" style="font-size: 80px; color: #ccc;"></i>
                                        <?php endif; ?>
                                        
                                        <?php if ($product['is_featured']): ?>
                                            <span class="product-badge" style="background: var(--accent-color);">Featured</span>
                                        <?php endif; ?>
                                        
                                        <?php if (is_logged_in()): ?>
                                            <button class="product-wishlist" onclick="toggleWishlist(<?php echo $product['product_id']; ?>)">
                                                <i class="bi bi-heart"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="product-info">
                                        <p class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></p>
                                        <h5 class="product-name">
                                            <a href="<?php echo SITE_URL; ?>/product.php?id=<?php echo $product['product_id']; ?>">
                                                <?php echo htmlspecialchars($product['product_name']); ?>
                                            </a>
                                        </h5>
                                        
                                        <div class="product-price">
                                            <span class="current-price"><?php echo format_price($product['price']); ?></span>
                                        </div>
                                        
                                        <div class="product-rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="bi bi-star<?php echo $i <= $rating['rating'] ? '-fill' : ''; ?>"></i>
                                            <?php endfor; ?>
                                            <span>(<?php echo $rating['count']; ?>)</span>
                                        </div>
                                        
                                        <button class="add-to-cart-btn" onclick="addToCart(<?php echo $product['product_id']; ?>)">
                                            <i class="bi bi-cart-plus"></i> Add to Cart
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">Previous</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <?php if ($i == $page): ?>
                                        <li class="page-item active">
                                            <span class="page-link"><?php echo $i; ?></span>
                                        </li>
                                    <?php elseif ($i == 1 || $i == $total_pages || ($i >= $page - 1 && $i <= $page + 1)): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php elseif ($i == $page - 2 || $i == $page + 2): ?>
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Next</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>

<script src="<?php echo ASSETS_PATH; ?>/js/main.js"></script>
<script>
function addToCart(productId) {
    <?php if (is_logged_in()): ?>
        fetch('<?php echo SITE_URL; ?>/api/add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'product_id=' + productId + '&quantity=1'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast(data.message || 'Failed to add product to cart', 'danger');
            }
        })
        .catch(error => {
            showToast('Error adding product to cart', 'danger');
        });
    <?php else: ?>
        showToast('Please login to add products to cart', 'warning');
        setTimeout(() => {
            window.location.href = '<?php echo SITE_URL; ?>/login.php';
        }, 1500);
    <?php endif; ?>
}

function toggleWishlist(productId) {
    fetch('<?php echo SITE_URL; ?>/api/toggle_wishlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'product_id=' + productId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message || 'Failed to update wishlist', 'danger');
        }
    })
    .catch(error => {
        showToast('Error updating wishlist', 'danger');
    });
}
</script>
