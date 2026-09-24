<?php
/**
 * Order Details Page
 */

require_once '../includes/functions.php';
require_once '../includes/auth.php';

$order_id = intval($_GET['id'] ?? 0);

if ($order_id <= 0) {
    set_flash_message('danger', 'Invalid order');
    redirect(SITE_URL . '/user/orders.php');
}

$user_id = $_SESSION['user_id'];

// Get order details
$sql = "SELECT * FROM orders WHERE order_id = ? AND user_id = ?";
$result = prepared_select($sql, "ii", [$order_id, $user_id]);

if ($result->num_rows === 0) {
    set_flash_message('danger', 'Order not found');
    redirect(SITE_URL . '/user/orders.php');
}

$order = $result->fetch_assoc();

$page_title = 'Order #' . $order['order_number'];
require_once '../includes/header.php';

// Get order items
$items_sql = "SELECT * FROM order_items WHERE order_id = ?";
$items_result = prepared_select($items_sql, "i", [$order_id]);
$order_items = [];
while ($row = $items_result->fetch_assoc()) {
    $order_items[] = $row;
}

$status_colors = [
    'pending' => 'warning',
    'processing' => 'info',
    'shipped' => 'primary',
    'delivered' => 'success',
    'cancelled' => 'danger'
];
$status_color = $status_colors[$order['order_status']] ?? 'secondary';
?>

<!-- Page Header -->
<section class="bg-success text-white py-4">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="fw-bold">Order Details</h1>
                <p class="mb-0">Order #<?php echo htmlspecialchars($order['order_number']); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Order Details Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px; font-size: 40px;">
                            <i class="bi bi-person"></i>
                        </div>
                        <h5><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></h5>
                        <p class="text-muted mb-3"><?php echo htmlspecialchars($_SESSION['email']); ?></p>
                        <div class="alert alert-success">
                            <i class="bi bi-currency-rupee me-1"></i>
                            <strong><?php echo $_SESSION['eco_points']; ?></strong> Eco Points
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <a href="<?php echo SITE_URL; ?>/user/index.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard
                            </a>
                            <a href="<?php echo SITE_URL; ?>/user/profile.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-person-gear me-2"></i> Profile
                            </a>
                            <a href="<?php echo SITE_URL; ?>/user/orders.php" class="list-group-item list-group-item-action active">
                                <i class="bi bi-bag me-2"></i> My Orders
                            </a>
                            <a href="<?php echo SITE_URL; ?>/user/wishlist.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-heart me-2"></i> Wishlist
                            </a>
                            <a href="<?php echo SITE_URL; ?>/cart.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-cart3 me-2"></i> Cart
                            </a>
                            <hr>
                            <a href="<?php echo SITE_URL; ?>/logout.php" class="list-group-item list-group-item-action text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-lg-9">
                <!-- Order Status -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1">Order Status</h5>
                                <small class="text-muted">Placed on <?php echo format_date($order['created_at'], 'd M Y, g:i A'); ?></small>
                            </div>
                            <span class="badge bg-<?php echo $status_color; ?> fs-5">
                                <?php echo ucfirst($order['order_status']); ?>
                            </span>
                        </div>
                        
                        <!-- Order Progress -->
                        <div class="mt-4">
                            <div class="progress" style="height: 30px;">
                                <?php
                                $progress_steps = ['pending', 'processing', 'shipped', 'delivered'];
                                $current_step_index = array_search($order['order_status'], $progress_steps);
                                if ($current_step_index === false) $current_step_index = 0;
                                $progress_percent = (($current_step_index + 1) / count($progress_steps)) * 100;
                                ?>
                                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $progress_percent; ?>%">
                                    <?php echo ucfirst($order['order_status']); ?>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-3 text-center">
                                    <small class="<?php echo $current_step_index >= 0 ? 'text-success' : 'text-muted'; ?>">Pending</small>
                                </div>
                                <div class="col-3 text-center">
                                    <small class="<?php echo $current_step_index >= 1 ? 'text-success' : 'text-muted'; ?>">Processing</small>
                                </div>
                                <div class="col-3 text-center">
                                    <small class="<?php echo $current_step_index >= 2 ? 'text-success' : 'text-muted'; ?>">Shipped</small>
                                </div>
                                <div class="col-3 text-center">
                                    <small class="<?php echo $current_step_index >= 3 ? 'text-success' : 'text-muted'; ?>">Delivered</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Order Items -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="mb-4">Order Items</h5>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($order_items as $item): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                            </td>
                                            <td><?php echo format_price($item['price']); ?></td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td><?php echo format_price($item['subtotal']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Shipping & Payment Info -->
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h5 class="mb-3">Shipping Information</h5>
                                <p class="mb-1"><strong>Address:</strong></p>
                                <p class="mb-2"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                                <p class="mb-1"><?php echo htmlspecialchars($order['city']); ?>, <?php echo htmlspecialchars($order['state']); ?></p>
                                <p class="mb-1"><?php echo htmlspecialchars($order['postal_code']); ?></p>
                                <p class="mb-0"><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h5 class="mb-3">Payment Information</h5>
                                <p class="mb-1"><strong>Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
                                <p class="mb-1"><strong>Status:</strong> 
                                    <span class="badge bg-<?php echo $order['payment_status'] == 'paid' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($order['payment_status']); ?>
                                    </span>
                                </p>
                                <?php if ($order['notes']): ?>
                                    <p class="mb-0"><strong>Notes:</strong> <?php echo htmlspecialchars($order['notes']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="mb-4">Order Summary</h5>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <span><?php echo format_price($order['total_amount']); ?></span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Discount</span>
                            <span><?php echo format_price($order['discount_amount']); ?></span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping</span>
                            <span class="text-success">FREE</span>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">Total</span>
                            <span class="fw-bold text-success fs-5"><?php echo format_price($order['final_amount']); ?></span>
                        </div>
                        
                        <?php if ($order['eco_points_earned'] > 0): ?>
                            <div class="alert alert-success mt-3">
                                <i class="bi bi-currency-rupee me-1"></i>
                                You earned <strong><?php echo $order['eco_points_earned']; ?> Eco Points</strong> from this order!
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="d-flex gap-2 mt-4">
                    <a href="<?php echo SITE_URL; ?>/user/orders.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Orders
                    </a>
                    <a href="<?php echo SITE_URL; ?>/shop.php" class="btn btn-success">
                        <i class="bi bi-cart-plus"></i> Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
