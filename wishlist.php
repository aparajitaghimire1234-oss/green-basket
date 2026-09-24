<?php
/**
 * Customer Wishlist Page
 */

require_once '../includes/functions.php';
require_once '../includes/auth.php';

$page_title = 'My Wishlist';
require_once '../includes/header.php';

$user_id = $_SESSION['user_id'];

// Handle remove from wishlist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove') {
    $wishlist_id = intval($_POST['wishlist_id'] ?? 0);
    
    if ($wishlist_id > 0) {
        $sql = "DELETE FROM wishlist WHERE wishlist_id = ? AND user_id = ?";
        prepared_execute($sql, "ii", [$wishlist_id, $user_id]);
        set_flash_message('success', 'Item removed from wishlist');
        redirect(SITE_URL . '/user/wishlist.php');
    }
}

// Get wishlist items
$sql = "SELECT w.wishlist_id, w.product_id, p.*, c.category_name 
        FROM wishlist w 
        LEFT JOIN products p ON w.product_id = p.product_id 
        LEFT JOIN categories c ON p.category_id = c.category_id 
        WHERE w.user_id = ? AND p.is_active = 1 
        ORDER BY w.created_at DESC";
$result = prepared_select($sql, "i", [$user_id]);

$wishlist_items = [];
while ($row = $result->fetch_assoc()) {
    $wishlist_items[] = $row;
}
?>

<!-- Page Header -->
<section class="bg-success text-white py-4">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="fw-bold">My Wishlist</h1>
                <p class="mb-0"><?php echo count($wishlist_items); ?> item(s) saved</p>
            </div>
        </div>
    </div>
</section>

<!-- Wishlist Content -->
<section class="py-5">
    <div class="container">
        <?php if (empty($wishlist_items)): ?>
            <div class="text-center py-5">
                <i class="bi bi-heart" style="font-size: 100px; color: #ccc;"></i>
                <h3 class="mt-4">Your wishlist is empty</h3>
                <p class="text-muted">Save your favorite products for later!</p>
                <a href="<?php echo SITE_URL; ?>/shop.php" class="btn btn-success btn-lg">Browse Products</a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($wishlist_items as $item): ?>
                    <?php
                    $rating = get_average_rating($item['product_id']);
                    $discount = 0;
                    if ($item['original_price'] && $item['original_price'] > $item['price']) {
                        $discount = round((($item['original_price'] - $item['price']) / $item['original_price']) * 100);
                    }
                    ?>
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                        <div class="product-card">
                            <div class="product-image">
                                <?php if ($item['image']): ?>
                                    <img src="<?php echo UPLOADS_URL . '/products/' . htmlspecialchars($item['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                <?php else: ?>
                                    <i class="bi bi-box-seam" style="font-size: 80px; color: #ccc;"></i>
                                <?php endif; ?>
                                
                                <?php if ($discount > 0): ?>
                                    <span class="product-badge"><?php echo $discount; ?>% OFF</span>
                                <?php endif; ?>
                                
                                <button class="product-wishlist active" onclick="removeFromWishlist(<?php echo $item['wishlist_id']; ?>)">
                                    <i class="bi bi-heart-fill"></i>
                                </button>
                            </div>
                            
                            <div class="product-info">
                                <p class="product-category"><?php echo htmlspecialchars($item['category_name']); ?></p>
                                <h5 class="product-name">
                                    <a href="<?php echo SITE_URL; ?>/product.php?id=<?php echo $item['product_id']; ?>">
                                        <?php echo htmlspecialchars($item['product_name']); ?>
                                    </a>
                                </h5>
                                
                                <div class="product-price">
                                    <span class="current-price"><?php echo format_price($item['price']); ?></span>
                                    <?php if ($item['original_price'] && $item['original_price'] > $item['price']): ?>
                                        <span class="original-price"><?php echo format_price($item['original_price']); ?></span>
                                        <span class="discount"><?php echo $discount; ?>%</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="product-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi bi-star<?php echo $i <= $rating['rating'] ? '-fill' : ''; ?>"></i>
                                    <?php endfor; ?>
                                    <span>(<?php echo $rating['count']; ?>)</span>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button class="add-to-cart-btn" onclick="addToCart(<?php echo $item['product_id']; ?>)">
                                        <i class="bi bi-cart-plus"></i> Add to Cart
                                    </button>
                                    <a href="<?php echo SITE_URL; ?>/product.php?id=<?php echo $item['product_id']; ?>" 
                                       class="btn btn-outline-secondary btn-sm">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>

<script>
function addToCart(productId) {
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
}

function removeFromWishlist(wishlistId) {
    if (confirm('Remove this item from wishlist?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'remove';
        
        const wishlistIdInput = document.createElement('input');
        wishlistIdInput.type = 'hidden';
        wishlistIdInput.name = 'wishlist_id';
        wishlistIdInput.value = wishlistId;
        
        form.appendChild(actionInput);
        form.appendChild(wishlistIdInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
