<?php
/**
 * GreenBasket Homepage
 * Main landing page with all sections
 */

require_once 'includes/functions.php';

$page_title = 'Home';
require_once 'includes/header.php';

// Get featured products
$featured_products = get_featured_products(8);

// Get all categories
$categories = get_all_categories();
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container hero-content">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <h1 class="hero-title">Shop Sustainably, Live Responsibly</h1>
                <p class="hero-subtitle">Discover eco-friendly products that help you reduce your carbon footprint without compromising on quality or style.</p>
                <a href="<?php echo SITE_URL; ?>/shop.php" class="btn btn-light btn-lg hero-btn">Shop Now <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="col-lg-5 d-none d-lg-block">
                <div class="text-center">
                    <i class="bi bi-basket2" style="font-size: 200px; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="py-5">
    <div class="container">
        <h2 class="section-title">Featured Products</h2>
        <p class="section-subtitle">Hand-picked eco-friendly products for sustainable living</p>
        
        <div class="row">
            <?php foreach ($featured_products as $product): ?>
                <?php
                $rating = get_average_rating($product['product_id']);
                $discount = 0;
                if ($product['original_price'] && $product['original_price'] > $product['price']) {
                    $discount = round((($product['original_price'] - $product['price']) / $product['original_price']) * 100);
                }
                ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="product-card">
                        <div class="product-image">
                            <?php if ($product['image']): ?>
                                <img src="<?php echo UPLOADS_URL . '/products/' . htmlspecialchars($product['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                            <?php else: ?>
                                <i class="bi bi-box-seam" style="font-size: 80px; color: #ccc;"></i>
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
        
        <div class="text-center mt-4">
            <a href="<?php echo SITE_URL; ?>/shop.php" class="btn btn-outline-success btn-lg">View All Products</a>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="section-title">Shop by Category</h2>
        <p class="section-subtitle">Browse our wide range of eco-friendly categories</p>
        
        <div class="row">
            <?php foreach ($categories as $category): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="category-card" onclick="window.location.href='<?php echo SITE_URL; ?>/shop.php?category=<?php echo $category['category_id']; ?>'">
                        <div class="category-image">
                            <i class="bi bi-<?php echo get_category_icon($category['category_name']); ?>"></i>
                        </div>
                        <div class="category-info">
                            <h5 class="category-name"><?php echo htmlspecialchars($category['category_name']); ?></h5>
                            <p class="category-count">View Products</p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="py-5">
    <div class="container">
        <h2 class="section-title">Why Choose GreenBasket?</h2>
        <p class="section-subtitle">Making sustainable shopping easy and affordable</p>
        
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-leaf"></i>
                    </div>
                    <h4 class="feature-title">100% Eco-Friendly</h4>
                    <p class="feature-description">All our products are carefully selected to ensure they are truly sustainable and environmentally friendly.</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-truck"></i>
                    </div>
                    <h4 class="feature-title">Free Shipping</h4>
                    <p class="feature-description">Enjoy free shipping on all orders above Rs999. We use eco-friendly packaging materials.</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h4 class="feature-title">Quality Assured</h4>
                    <p class="feature-description">Every product is quality checked to ensure you receive only the best eco-friendly items.</p>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="bi bi-currency-rupee"></i>
                    </div>
                    <h4 class="feature-title">Affordable Prices</h4>
                    <p class="feature-description">Sustainable living shouldn't break the bank. We offer competitive prices on all products.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Eco Tips Section -->
<section class="eco-tips-section">
    <div class="container">
        <h2 class="section-title">Daily Eco Tips</h2>
        <p class="section-subtitle">Simple tips to live a more sustainable life</p>
        
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="tip-card">
                    <div class="tip-icon">
                        <i class="bi bi-droplet"></i>
                    </div>
                    <h5 class="tip-title">Save Water</h5>
                    <p class="tip-text">Turn off the tap while brushing teeth. This simple act can save up to 8 gallons of water per day.</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="tip-card">
                    <div class="tip-icon">
                        <i class="bi bi-lightbulb"></i>
                    </div>
                    <h5 class="tip-title">Energy Efficient</h5>
                    <p class="tip-text">Switch to LED bulbs. They use 75% less energy and last 25 times longer than incandescent bulbs.</p>
                </div>
            </div>
            
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="tip-card">
                    <div class="tip-icon">
                        <i class="bi bi-bag"></i>
                    </div>
                    <h5 class="tip-title">Reduce Plastic</h5>
                    <p class="tip-text">Carry a reusable bag when shopping. One reusable bag can replace over 700 plastic bags annually.</p>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <a href="<?php echo SITE_URL; ?>/eco-tips.php" class="btn btn-success btn-lg">More Eco Tips</a>
        </div>
    </div>
</section>

<!-- Best Sellers Section -->
<section class="py-5">
    <div class="container">
        <h2 class="section-title">Best Sellers</h2>
        <p class="section-subtitle">Our most popular eco-friendly products</p>
        
        <div class="row">
            <?php 
            $best_sellers = array_slice($featured_products, 0, 4);
            foreach ($best_sellers as $product): 
                $rating = get_average_rating($product['product_id']);
            ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="product-card">
                        <div class="product-image">
                            <?php if ($product['image']): ?>
                                <img src="<?php echo UPLOADS_URL . '/products/' . htmlspecialchars($product['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                            <?php else: ?>
                                <i class="bi bi-box-seam" style="font-size: 80px; color: #ccc;"></i>
                            <?php endif; ?>
                            
                            <span class="product-badge">Best Seller</span>
                            
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
    </div>
</section>

<?php
/**
 * Helper function to get category icon
 */
function get_category_icon($category_name) {
    $icons = [
        'Eco Kitchen' => 'cup-hot',
        'Organic Products' => 'egg-fried',
        'Personal Care' => 'heart-pulse',
        'Gardening' => 'flower1',
        'Home Essentials' => 'house',
        'Eco Stationery' => 'pencil',
        'Reusable Products' => 'arrow-repeat'
    ];
    return $icons[$category_name] ?? 'tag';
}
?>

<!-- Footer (homepage only) -->
<footer class="bg-dark text-white mt-5">
    <div class="container py-5">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h4 class="text-success mb-3"><i class="bi bi-basket-fill"></i> GreenBasket</h4>
                <p>Your one-stop shop for eco-friendly and sustainable products. Join us in making the world a greener place, one purchase at a time.</p>
            </div>
            <div class="col-md-2 mb-4">
                <h5 class="mb-3">Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="<?php echo SITE_URL; ?>/index.php" class="text-white text-decoration-none">Home</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/shop.php" class="text-white text-decoration-none">Shop</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/about.php" class="text-white text-decoration-none">About Us</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/contact.php" class="text-white text-decoration-none">Contact</a></li>
                </ul>
            </div>
            <div class="col-md-2 mb-4">
                <h5 class="mb-3">Customer Service</h5>
                <ul class="list-unstyled">
                    <li><a href="#" class="text-white text-decoration-none">FAQ</a></li>
                    <li><a href="#" class="text-white text-decoration-none">Shipping Info</a></li>
                    <li><a href="#" class="text-white text-decoration-none">Returns</a></li>
                    <li><a href="#" class="text-white text-decoration-none">Privacy Policy</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-4">
                <h5 class="mb-3">Newsletter</h5>
                <p>Subscribe to get eco tips and exclusive offers!</p>
                <form class="d-flex">
                    <input type="email" class="form-control me-2" placeholder="Your email">
                    <button type="submit" class="btn btn-success">Subscribe</button>
                </form>
            </div>
        </div>
        <hr class="my-4">
        <div class="row">
            <div class="col-md-6">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> GreenBasket. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-end">
                <p class="mb-0">Made with <i class="bi bi-heart-fill text-danger"></i> for a greener planet</p>
            </div>
        </div>
    </div>
</footer>

<?php
require_once 'includes/footer.php';
?>

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
                alert('Product added to cart!');
                location.reload();
            } else {
                alert(data.message || 'Failed to add product to cart');
            }
        })
        .catch(error => {
            alert('Error adding product to cart');
        });
    <?php else: ?>
        alert('Please login to add products to cart');
        window.location.href = '<?php echo SITE_URL; ?>/login.php';
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
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || 'Failed to update wishlist');
        }
    })
    .catch(error => {
        alert('Error updating wishlist');
    });
}
</script>