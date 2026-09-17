<?php
/**
 * Checkout Page
 */

require_once 'includes/functions.php';
require_once 'includes/auth.php';

$page_title = 'Checkout';

$user_id = $_SESSION['user_id'];

// Get user details
$user = get_user($user_id);

// Get cart items
$sql = "SELECT c.cart_id, c.quantity, p.*, c.quantity * p.price as subtotal 
        FROM cart c 
        LEFT JOIN products p ON c.product_id = p.product_id 
        WHERE c.user_id = ? AND p.is_active = 1";
$result = prepared_select($sql, "i", [$user_id]);

$cart_items = [];
$total_amount = 0;

while ($row = $result->fetch_assoc()) {
    // Check stock
    if ($row['stock_quantity'] < $row['quantity']) {
        set_flash_message('danger', 'Some items in your cart are out of stock. Please update your cart.');
        redirect(SITE_URL . '/cart.php');
    }
    $cart_items[] = $row;
    $total_amount += $row['subtotal'];
}

if (empty($cart_items)) {
    set_flash_message('warning', 'Your cart is empty');
    redirect(SITE_URL . '/shop.php');
}

// Calculate shipping
$shipping_cost = $total_amount >= 999 ? 0 : 50;
$final_amount = $total_amount + $shipping_cost;

// Calculate eco points
$eco_points_earned = calculate_eco_points($total_amount);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $shipping_address = sanitize_input($_POST['shipping_address'] ?? '');
    $city = sanitize_input($_POST['city'] ?? '');
    $state = sanitize_input($_POST['state'] ?? '');
    $postal_code = sanitize_input($_POST['postal_code'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $payment_method = sanitize_input($_POST['payment_method'] ?? 'COD');
    $notes = sanitize_input($_POST['notes'] ?? '');

    // Validation
    if (empty($shipping_address)) {
        $errors[] = 'Shipping address is required';
    }
    if (empty($city)) {
        $errors[] = 'City is required';
    }
    if (empty($state)) {
        $errors[] = 'State is required';
    }
    if (empty($postal_code)) {
        $errors[] = 'Postal code is required';
    }
    if (empty($phone)) {
        $errors[] = 'Phone number is required';
    } elseif (!validate_phone($phone)) {
        $errors[] = 'Invalid phone number';
    }

    // Process order if no errors
    if (empty($errors)) {
        // If Khalti payment, redirect to Khalti initiation
        if ($payment_method === 'Khalti') {
            // Store order data in session for Khalti callback
            $_SESSION['pending_order'] = [
                'user_id' => $user_id,
                'total_amount' => $total_amount,
                'final_amount' => $final_amount,
                'shipping_address' => $shipping_address,
                'city' => $city,
                'state' => $state,
                'postal_code' => $postal_code,
                'phone' => $phone,
                'payment_method' => $payment_method,
                'notes' => $notes,
                'eco_points_earned' => $eco_points_earned,
                'cart_items' => $cart_items
            ];
            
            // Redirect to Khalti payment initiation
            redirect(SITE_URL . '/khalti-initiate.php');
        }
        
        // For COD, process order directly
        $order_number = generate_order_number();

        // Insert order
        $order_sql = "INSERT INTO orders (user_id, order_number, total_amount, discount_amount, final_amount, 
                      shipping_address, city, state, postal_code, phone, order_status, payment_method, 
                      payment_status, notes, eco_points_earned) 
                      VALUES (?, ?, ?, 0, ?, ?, ?, ?, ?, ?, 'pending', ?, 'pending', ?, ?)";
        
        if (prepared_execute($order_sql, "isddsssssssi", [
            $user_id, $order_number, $total_amount, $final_amount, 
            $shipping_address, $city, $state, $postal_code, $phone, 
            $payment_method, $notes, $eco_points_earned
        ])) {
            $order_id = get_last_insert_id();

            // Insert order items
            foreach ($cart_items as $item) {
                $item_sql = "INSERT INTO order_items (order_id, product_id, product_name, quantity, price, subtotal) 
                            VALUES (?, ?, ?, ?, ?, ?)";
                prepared_execute($item_sql, "iisidd", [
                    $order_id, $item['product_id'], $item['product_name'], 
                    $item['quantity'], $item['price'], $item['subtotal']
                ]);

                // Update product stock
                $stock_sql = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?";
                prepared_execute($stock_sql, "ii", [$item['quantity'], $item['product_id']]);
            }

            // Add eco points to user
            $points_sql = "UPDATE users SET eco_points = eco_points + ? WHERE user_id = ?";
            prepared_execute($points_sql, "ii", [$eco_points_earned, $user_id]);

            // Log eco points
            $eco_log_sql = "INSERT INTO eco_points (user_id, points, description, order_id) VALUES (?, ?, ?, ?)";
            prepared_execute($eco_log_sql, "iisi", [$user_id, $eco_points_earned, "Order #$order_number", $order_id]);

            // Clear cart
            $clear_cart_sql = "DELETE FROM cart WHERE user_id = ?";
            prepared_execute($clear_cart_sql, "i", [$user_id]);

            // Update session
            $_SESSION['eco_points'] += $eco_points_earned;

            set_flash_message('success', "Order placed successfully! Order #$order_number");
            redirect(SITE_URL . '/user/orders.php');
        } else {
            $errors[] = 'Failed to place order. Please try again.';
        }
    }
}

require_once 'includes/header.php';
?>

<!-- Page Header -->
<section class="bg-success text-white py-4">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="fw-bold">Checkout</h1>
                <p class="mb-0">Complete your order</p>
            </div>
        </div>
    </div>
</section>

<!-- Checkout Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Checkout Form -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h5 class="mb-4">Shipping Information</h5>
                        
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>" readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Shipping Address *</label>
                                <textarea class="form-control" name="shipping_address" rows="3" required><?php echo htmlspecialchars($shipping_address ?? $user['address'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">City *</label>
                                    <input type="text" class="form-control" name="city" 
                                           value="<?php echo htmlspecialchars($city ?? $user['city'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">State *</label>
                                    <input type="text" class="form-control" name="state" 
                                           value="<?php echo htmlspecialchars($state ?? $user['state'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Postal Code *</label>
                                    <input type="text" class="form-control" name="postal_code" 
                                           value="<?php echo htmlspecialchars($postal_code ?? $user['postal_code'] ?? ''); ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control" name="phone" 
                                       value="<?php echo htmlspecialchars($phone ?? $user['phone'] ?? ''); ?>" 
                                       placeholder="10-digit mobile number" required>
                            </div>
                            
                            <hr>
                            
                            <h5 class="mb-4">Payment Method</h5>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="cod" value="COD" checked>
                                    <label class="form-check-label" for="cod">
                                        <strong>Cash on Delivery (COD)</strong>
                                        <small class="d-block text-muted">Pay when your order arrives</small>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="khalti" value="Khalti">
                                    <label class="form-check-label" for="khalti">
                                        <strong>Khalti</strong>
                                        <small class="d-block text-muted">Pay instantly using Khalti ePayment</small>
                                    </label>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="mb-3">
                                <label class="form-label">Order Notes (Optional)</label>
                                <textarea class="form-control" name="notes" rows="2" placeholder="Any special instructions for your order..."><?php echo htmlspecialchars($notes ?? ''); ?></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-success btn-lg w-100">
                                <i class="bi bi-lock-fill"></i> Place Order
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="mb-4">Order Summary</h5>
                        
                        <?php foreach ($cart_items as $item): ?>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                    <small class="text-muted">Qty: <?php echo $item['quantity']; ?></small>
                                </div>
                                <span class="fw-bold"><?php echo format_price($item['subtotal']); ?></span>
                            </div>
                        <?php endforeach; ?>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <span><?php echo format_price($total_amount); ?></span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping</span>
                            <span class="<?php echo $shipping_cost == 0 ? 'text-success' : ''; ?>">
                                <?php echo $shipping_cost == 0 ? 'FREE' : format_price($shipping_cost); ?>
                            </span>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold">Total</span>
                            <span class="fw-bold text-success fs-5"><?php echo format_price($final_amount); ?></span>
                        </div>
                        
                        <div class="alert alert-success small">
                            <i class="bi bi-currency-rupee me-1"></i>
                            You'll earn <strong><?php echo $eco_points_earned; ?> Eco Points</strong> on this order!
                        </div>
                        
                        <div class="card bg-light border-0">
                            <div class="card-body">
                                <h6 class="mb-3"><i class="bi bi-shield-check me-2"></i>Secure Checkout</h6>
                                <ul class="list-unstyled mb-0 small">
                                    <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-1"></i>SSL Encrypted</li>
                                    <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-1"></i>Safe Transactions</li>
                                    <li class="mb-0"><i class="bi bi-check-circle-fill text-success me-1"></i>Privacy Protected</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>