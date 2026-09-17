<?php
/**
 * Product Details Page
 */

require_once 'includes/functions.php';

$product_id = intval($_GET['id'] ?? 0);

if ($product_id <= 0) {
    set_flash_message('danger', 'Invalid product');
    redirect(SITE_URL . '/shop.php');
}

$product = get_product($product_id);

if (!$product || !$product['is_active']) {
    set_flash_message('danger', 'Product not found');
    redirect(SITE_URL . '/shop.php');
}

$page_title = $product['product_name'];
require_once 'includes/header.php';

$rating = get_average_rating($product_id);
$discount = 0;
if ($product['original_price'] && $product['original_price'] > $product['price']) {
    $discount = round((($product['original_price'] - $product['price']) / $product['original_price']) * 100);
}

// Get related products from same category
$related_products = get_products_by_category($product['category_id'], 4);
// Remove current product from related
$related_products = array_filter($related_products, function($p) use ($product_id) {
    return $p['product_id'] != $product_id;
});

// Get product reviews
$reviews_sql = "SELECT r.*, u.first_name, u.last_name 
                FROM reviews r 
                LEFT JOIN users u ON r.user_id = u.user_id 
                WHERE r.product_id = ? 
                ORDER BY r.created_at DESC 
                LIMIT 5";
$reviews_result = prepared_select($reviews_sql, "i", [$product_id]);
$reviews = [];
while ($row = $reviews_result->fetch_assoc()) {
    $reviews[] = $row;
}
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-2">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/shop.php">Shop</a></li>
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/shop.php?category=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['product_name']); ?></li>
        </ol>
    </div>
</nav>

<!-- Product Details -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Product Image -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="product-image-large bg-light rounded-3 d-flex align-items-center justify-content-center" style="height: 400px;">
                            <?php if ($product['image']): ?>
                                <img src="<?php echo UPLOADS_URL . '/products/' . htmlspecialchars($product['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                                     class="img-fluid" style="max-height: 380px;">
                            <?php else: ?>
                                <i class="bi bi-box-seam" style="font-size: 150px; color: #ccc;"></i>
                            <?php endif; ?>
                        </div>
                </div>
            </div>
            
            <!-- Product Info -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <p class="text-success fw-bold mb-2"><?php echo htmlspecialchars($product['category_name']); ?></p>
                        <h1 class="mb-3"><?php echo htmlspecialchars($product['product_name']); ?></h1>
                        
                        <div class="mb-3">
                            <div class="product-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi bi-star<?php echo $i <= $rating['rating'] ? '-fill' : ''; ?>"></i>
                                <?php endfor; ?>
                                <span class="ms-2"><?php echo $rating['rating']; ?> out of 5</span>
                                <span class="text-muted">(<?php echo $rating['count']; ?> reviews)</span>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <div class="d-flex align-items-center gap-3">
                                <span class="display-6 fw-bold text-success"><?php echo format_price($product['price']); ?></span>
                            </div>
                        </div>
                        
                        <p class="text-muted mb-4"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                        
                        <div class="mb-4">
                            <p class="mb-1"><strong>SKU:</strong> <?php echo htmlspecialchars($product['sku'] ?? 'N/A'); ?></p>
                            <p class="mb-1"><strong>Availability:</strong> 
                                <?php if ($product['stock_quantity'] > 10): ?>
                                    <span class="text-success">In Stock (<?php echo $product['stock_quantity']; ?> available)</span>
                                <?php elseif ($product['stock_quantity'] > 0): ?>
                                    <span class="text-warning">Low Stock (<?php echo $product['stock_quantity']; ?> available)</span>
                                <?php else: ?>
                                    <span class="text-danger">Out of Stock</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Quantity</label>
                            <div class="input-group" style="max-width: 150px;">
                                <button class="btn btn-outline-secondary" type="button" onclick="decrementQuantity()">-</button>
                                <input type="number" class="form-control text-center" id="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>">
                                <button class="btn btn-outline-secondary" type="button" onclick="incrementQuantity()">+</button>
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2 mb-4">
                            <?php if ($product['stock_quantity'] > 0): ?>
                                <button class="btn btn-success btn-lg" onclick="addToCart(<?php echo $product['product_id']; ?>)">
                                    <i class="bi bi-cart-plus"></i> Add to Cart
                                </button>
                            <?php else: ?>
                                <button class="btn btn-secondary btn-lg" disabled>
                                    <i class="bi bi-cart-x"></i> Out of Stock
                                </button>
                            <?php endif; ?>
                            
                            <?php if (is_logged_in()): ?>
                                <button class="btn btn-outline-danger" onclick="toggleWishlist(<?php echo $product['product_id']; ?>)">
                                    <i class="bi bi-heart"></i> Add to Wishlist
                                </button>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <h6 class="mb-3"><i class="bi bi-info-circle me-2"></i>Product Highlights</h6>
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>100% Eco-Friendly</li>
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Sustainably Sourced</li>
                                    <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Chemical-Free</li>
                                    <li class="mb-0"><i class="bi bi-check-circle-fill text-success me-2"></i>Earn <?php echo calculate_eco_points($product['price']); ?> Eco Points</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Product Reviews -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="section-title">Customer Reviews</h2>
        <p class="section-subtitle">See what our customers are saying</p>
        
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <?php if (empty($reviews)): ?>
                    <div class="text-center py-4">
                        <p class="text-muted">No reviews yet. Be the first to review this product!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-0"><?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></h6>
                                        <small class="text-muted"><?php echo format_date($review['created_at']); ?></small>
                                    </div>
                                    <div class="text-warning">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi bi-star<?php echo $i <= $review['rating'] ? '-fill' : ''; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <?php if (is_logged_in()): ?>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="mb-3">Write a Review</h5>
                            <form id="reviewForm">
                                <div class="mb-3">
                                    <label class="form-label">Rating</label>
                                    <div class="star-rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi bi-star star" data-rating="<?php echo $i; ?>"></i>
                                        <?php endfor; ?>
                                        <input type="hidden" id="rating" name="rating" value="5">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Your Review</label>
                                    <textarea class="form-control" id="reviewText" name="review_text" rows="4" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-success">Submit Review</button>
                            </form>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center mt-4">
                        <p>Please <a href="<?php echo SITE_URL; ?>/login.php">login</a> to write a review.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Related Products -->
<?php if (!empty($related_products)): ?>
<section class="py-5">
    <div class="container">
        <h2 class="section-title">Related Products</h2>
        <p class="section-subtitle">You may also like</p>
        
        <div class="row">
            <?php foreach (array_slice($related_products, 0, 4) as $related_product): ?>
                <?php
                $related_rating = get_average_rating($related_product['product_id']);
                $related_discount = 0;
                if ($related_product['original_price'] && $related_product['original_price'] > $related_product['price']) {
                    $related_discount = round((($related_product['original_price'] - $related_product['price']) / $related_product['original_price']) * 100);
                }
                ?>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="product-card">
                        <div class="product-image">
                            <?php if ($related_product['image']): ?>
                                <img src="<?php echo UPLOADS_URL . '/products/' . htmlspecialchars($related_product['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($related_product['product_name']); ?>">
                            <?php else: ?>
                                <i class="bi bi-box-seam" style="font-size: 80px; color: #ccc;"></i>
                            <?php endif; ?>
                            
                            <?php if (is_logged_in()): ?>
                                <button class="product-wishlist" onclick="toggleWishlist(<?php echo $related_product['product_id']; ?>)">
                                    <i class="bi bi-heart"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-info">
                            <p class="product-category"><?php echo htmlspecialchars($related_product['category_name']); ?></p>
                            <h5 class="product-name">
                                <a href="<?php echo SITE_URL; ?>/product.php?id=<?php echo $related_product['product_id']; ?>">
                                    <?php echo htmlspecialchars($related_product['product_name']); ?>
                                </a>
                            </h5>
                            
                            <div class="product-price">
                                <span class="current-price"><?php echo format_price($related_product['price']); ?></span>
                            </div>
                            
                            <div class="product-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi bi-star<?php echo $i <= $related_rating['rating'] ? '-fill' : ''; ?>"></i>
                                <?php endfor; ?>
                                <span>(<?php echo $related_rating['count']; ?>)</span>
                            </div>
                            
                            <button class="add-to-cart-btn" onclick="addToCart(<?php echo $related_product['product_id']; ?>)">
                                <i class="bi bi-cart-plus"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>

<style>
.star-rating .star {
    font-size: 24px;
    cursor: pointer;
    color: #ccc;
}

.star-rating .star.active,
.star-rating .star:hover {
    color: var(--accent-color);
}
</style>

<script>
const maxQuantity = <?php echo $product['stock_quantity']; ?>;

function incrementQuantity() {
    const input = document.getElementById('quantity');
    if (parseInt(input.value) < maxQuantity) {
        input.value = parseInt(input.value) + 1;
    }
}

function decrementQuantity() {
    const input = document.getElementById('quantity');
    if (parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
    }
}

function addToCart(productId) {
    <?php if (is_logged_in()): ?>
        const quantity = document.getElementById('quantity').value;
        fetch('<?php echo SITE_URL; ?>/api/add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'product_id=' + productId + '&quantity=' + quantity
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

// Star rating
document.querySelectorAll('.star-rating .star').forEach(star => {
    star.addEventListener('click', function() {
        const rating = this.dataset.rating;
        document.getElementById('rating').value = rating;
        document.querySelectorAll('.star-rating .star').forEach(s => {
            s.classList.remove('active');
            if (s.dataset.rating <= rating) {
                s.classList.add('active');
            }
        });
    });
});

// Submit review
document.getElementById('reviewForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const rating = document.getElementById('rating').value;
    const reviewText = document.getElementById('reviewText').value;
    
    fetch('<?php echo SITE_URL; ?>/api/add_review.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'product_id=<?php echo $product_id; ?>&rating=' + rating + '&review_text=' + encodeURIComponent(reviewText)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Review submitted successfully!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message || 'Failed to submit review', 'danger');
        }
    })
    .catch(error => {
        showToast('Error submitting review', 'danger');
    });
});
</script>
