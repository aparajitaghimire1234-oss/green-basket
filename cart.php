<?php
/**
 * Shopping Cart Page
 */

require_once 'includes/functions.php';
require_once 'includes/auth.php';

$page_title = 'Shopping Cart';

$user_id = $_SESSION['user_id'];

// Handle cart updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize_input($_POST['action'] ?? '');
    
    if ($action === 'update') {
        $cart_id = intval($_POST['cart_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 1);
        
        if ($cart_id > 0 && $quantity > 0) {
            // Get cart item to check stock
            $sql = "SELECT c.product_id, c.quantity, p.stock_quantity 
                    FROM cart c 
                    LEFT JOIN products p ON c.product_id = p.product_id 
                    WHERE c.cart_id = ? AND c.user_id = ?";
            $result = prepared_select($sql, "ii", [$cart_id, $user_id]);
            
            if ($result->num_rows > 0) {
                $cart_item = $result->fetch_assoc();
                
                if ($quantity <= $cart_item['stock_quantity']) {
                    $update_sql = "UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?";
                    prepared_execute($update_sql, "iii", [$quantity, $cart_id, $user_id]);
                    set_flash_message('success', 'Cart updated successfully');
                } else {
                    set_flash_message('danger', 'Insufficient stock');
                }
            }
        }
        redirect(SITE_URL . '/cart.php');
    }
    
    if ($action === 'remove') {
        $cart_id = intval($_POST['cart_id'] ?? 0);
        
        if ($cart_id > 0) {
            $delete_sql = "DELETE FROM cart WHERE cart_id = ? AND user_id = ?";
            prepared_execute($delete_sql, "ii", [$cart_id, $user_id]);
            set_flash_message('success', 'Item removed from cart');
        }
        redirect(SITE_URL . '/cart.php');
    }
    
    if ($action === 'clear') {
        $delete_sql = "DELETE FROM cart WHERE user_id = ?";
        prepared_execute($delete_sql, "i", [$user_id]);
        set_flash_message('success', 'Cart cleared');
        redirect(SITE_URL . '/cart.php');
    }
}

require_once 'includes/header.php';

// Get cart items
$sql = "SELECT c.cart_id, c.quantity, p.*, cat.category_name, c.quantity * p.price as subtotal 
        FROM cart c 
        LEFT JOIN products p ON c.product_id = p.product_id 
        LEFT JOIN categories cat ON p.category_id = cat.category_id
        WHERE c.user_id = ? AND p.is_active = 1";
$result = prepared_select($sql, "i", [$user_id]);

$cart_items = [];
$total_amount = 0;

while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
    $total_amount += $row['subtotal'];
}
?>

<!-- Page Header -->
<section class="bg-success text-white py-4">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="fw-bold">Shopping Cart</h1>
                <p class="mb-0"><?php echo count($cart_items); ?> item(s) in your cart</p>
            </div>
        </div>
    </div>
</section>

<!-- Cart Content -->
<section class="py-5">
    <div class="container">
        <?php if (empty($cart_items)): ?>
            <div class="text-center py-5">
                <i class="bi bi-cart-x" style="font-size: 100px; color: #ccc;"></i>
                <h3 class="mt-4">Your cart is empty</h3>
                <p class="text-muted">Looks like you haven't added any items to your cart yet.</p>
                <a href="<?php echo SITE_URL; ?>/shop.php" class="btn btn-success btn-lg">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="row">
                <!-- Cart Items -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="p-3">Product</th>
                                            <th class="p-3">Price</th>
                                            <th class="p-3">Quantity</th>
                                            <th class="p-3">Subtotal</th>
                                            <th class="p-3">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cart_items as $item): ?>
                                            <tr>
                                                <td class="p-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-3" style="width: 80px; height: 80px;">
                                                            <?php if ($item['image']): ?>
                                                                <img src="<?php echo UPLOADS_URL . '/products/' . htmlspecialchars($item['image']); ?>" 
                                                                     alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                                                                     class="img-fluid rounded">
                                                            <?php else: ?>
                                                                <div class="bg-light rounded d-flex align-items-center justify-content-center h-100">
                                                                    <i class="bi bi-box-seam" style="font-size: 40px; color: #ccc;"></i>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1">
                                                                <a href="<?php echo SITE_URL; ?>/product.php?id=<?php echo $item['product_id']; ?>" class="text-decoration-none">
                                                                    <?php echo htmlspecialchars($item['product_name']); ?>
                                                                </a>
                                                            </h6>
                                                            <small class="text-muted"><?php echo htmlspecialchars($item['category_name']); ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="p-3">
                                                    <span class="fw-bold"><?php echo format_price($item['price']); ?></span>
                                                </td>
                                                <td class="p-3">
                                                    <form method="POST" action="" class="d-flex align-items-center">
                                                        <input type="hidden" name="action" value="update">
                                                        <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                                        <div class="input-group" style="max-width: 120px;">
                                                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="updateQuantity(<?php echo $item['cart_id']; ?>, <?php echo $item['quantity'] - 1; ?>)">-</button>
                                                            <input type="number" class="form-control form-control-sm text-center" name="quantity" 
                                                                   value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock_quantity']; ?>"
                                                                   id="qty-<?php echo $item['cart_id']; ?>">
                                                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="updateQuantity(<?php echo $item['cart_id']; ?>, <?php echo $item['quantity'] + 1; ?>)">+</button>
                                                        </div>
                                                    </form>
                                                    <?php if ($item['stock_quantity'] < 5): ?>
                                                        <small class="text-warning">Only <?php echo $item['stock_quantity']; ?> left!</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="p-3">
                                                    <span class="fw-bold text-success"><?php echo format_price($item['subtotal']); ?></span>
                                                </td>
                                                <td class="p-3">
                                                    <form method="POST" action="">
                                                        <input type="hidden" name="action" value="remove">
                                                        <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove this item from cart?')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-4">
                        <a href="<?php echo SITE_URL; ?>/shop.php" class="btn btn outline-secondary">
                            <i class="bi bi-arrow-left"></i> Continue Shopping
                        </a>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="clear">
                            <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Clear entire cart?')">
                                <i class="bi bi-trash"></i> Clear Cart
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Cart Summary -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="mb-4">Order Summary</h5>
                            
                            <div class="d-flex justify-content-between mb-3">
                                <span>Subtotal</span>
                                <span class="fw-bold"><?php echo format_price($total_amount); ?></span>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-3">
                                <span>Shipping</span>
                                <span class="text-success"><?php echo $total_amount >= 999 ? 'FREE' : format_price(50); ?></span>
                            </div>
                            
                            <?php if ($total_amount < 999): ?>
                                <div class="alert alert-info small mb-3">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Add <?php echo format_price(999 - $total_amount); ?> more for free shipping!
                                </div>
                            <?php endif; ?>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between mb-4">
                                <span class="fw-bold">Total</span>
                                <span class="fw-bold text-success fs-5"><?php echo format_price($total_amount + ($total_amount >= 999 ? 0 : 50)); ?></span>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Coupon Code</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Enter coupon code" id="couponCode">
                                    <button class="btn btn-outline-secondary" type="button" onclick="applyCoupon()">Apply</button>
                                </div>
                            </div>
                            
                            <a href="<?php echo SITE_URL; ?>/checkout.php" class="btn btn-success btn-lg w-100">
                                Proceed to Checkout <i class="bi bi-arrow-right"></i>
                            </a>
                            
                            <div class="mt-3 text-center">
                                <small class="text-muted">
                                    <i class="bi bi-shield-check me-1"></i>
                                    Secure checkout powered by SSL
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Eco Points Info -->
                    <div class="card bg-light border-0 mt-4">
                        <div class="card-body">
                            <h6 class="mb-3"><i class="bi bi-currency-rupee me-2"></i>Eco Points</h6>
                            <p class="small text-muted mb-2">You'll earn <strong><?php echo calculate_eco_points($total_amount); ?> Eco Points</strong> on this order</p>
                            <p class="small text-muted mb-0">Your current balance: <strong><?php echo $_SESSION['eco_points']; ?> points</strong></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>

<script>
function updateQuantity(cartId, quantity) {
    const input = document.getElementById('qty-' + cartId);
    if (quantity >= 1) {
        input.value = quantity;
        input.form.submit();
    }
}

function applyCoupon() {
    const couponCode = document.getElementById('couponCode').value;
    if (couponCode.trim()) {
        showToast('Coupon functionality coming soon!', 'info');
    } else {
        showToast('Please enter a coupon code', 'warning');
    }
}
</script>
