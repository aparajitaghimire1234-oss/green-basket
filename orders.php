<?php
/**
 * Customer Orders Page
 */

require_once '../includes/functions.php';
require_once '../includes/auth.php';

$page_title = 'My Orders';
require_once '../includes/header.php';

$user_id = $_SESSION['user_id'];

// Get all orders
$sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$result = prepared_select($sql, "i", [$user_id]);

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
?>

<!-- Page Header -->
<section class="bg-success text-white py-4">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="fw-bold">My Orders</h1>
                <p class="mb-0"><?php echo count($orders); ?> order(s)</p>
            </div>
        </div>
    </div>
</section>

<!-- Orders Content -->
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
                <?php if (empty($orders)): ?>
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-bag-x" style="font-size: 80px; color: #ccc;"></i>
                            <h3 class="mt-4">No orders yet</h3>
                            <p class="text-muted">You haven't placed any orders yet.</p>
                            <a href="<?php echo SITE_URL; ?>/shop.php" class="btn btn-success btn-lg">Start Shopping</a>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($orders as $order): ?>
                        <?php
                        $status_colors = [
                            'pending' => 'warning',
                            'processing' => 'info',
                            'shipped' => 'primary',
                            'delivered' => 'success',
                            'cancelled' => 'danger'
                        ];
                        $status_color = $status_colors[$order['order_status']] ?? 'secondary';
                        
                        // Get order items
                        $items_sql = "SELECT * FROM order_items WHERE order_id = ?";
                        $items_result = prepared_select($items_sql, "i", [$order['order_id']]);
                        $order_items = [];
                        while ($item_row = $items_result->fetch_assoc()) {
                            $order_items[] = $item_row;
                        }
                        ?>
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <div>
                                        <h5 class="mb-1">Order #<?php echo htmlspecialchars($order['order_number']); ?></h5>
                                        <small class="text-muted"><?php echo format_date($order['created_at'], 'd M Y, g:i A'); ?></small>
                                    </div>
                                    <span class="badge bg-<?php echo $status_color; ?> fs-6">
                                        <?php echo ucfirst($order['order_status']); ?>
                                    </span>
                                </div>
                                
                                <div class="row mb-3">
                                    <?php foreach (array_slice($order_items, 0, 3) as $item): ?>
                                        <div class="col-md-4 mb-2">
                                            <div class="d-flex align-items-center">
                                                <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                    <i class="bi bi-box-seam" style="font-size: 25px; color: #ccc;"></i>
                                                </div>
                                                <div>
                                                    <small class="fw-bold"><?php echo htmlspecialchars($item['product_name']); ?></small>
                                                    <br>
                                                    <small class="text-muted">Qty: <?php echo $item['quantity']; ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (count($order_items) > 3): ?>
                                        <div class="col-md-4 mb-2">
                                            <small class="text-muted">+<?php echo count($order_items) - 3; ?> more items</small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold">Total: <?php echo format_price($order['final_amount']); ?></span>
                                        <?php if ($order['eco_points_earned'] > 0): ?>
                                            <small class="text-success ms-2">+<?php echo $order['eco_points_earned']; ?> pts</small>
                                        <?php endif; ?>
                                    </div>
                                    <a href="<?php echo SITE_URL; ?>/user/order-details.php?id=<?php echo $order['order_id']; ?>" 
                                       class="btn btn-sm btn-outline-success">View Details</a>
                                </div>
                                
                                <div class="mt-3 pt-3 border-top">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <small class="text-muted">Payment Method:</small>
                                            <div class="fw-bold"><?php echo htmlspecialchars($order['payment_method']); ?></div>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted">Payment Status:</small>
                                            <div>
                                                <span class="badge <?php echo $order['payment_status'] == 'paid' ? 'bg-success' : 'bg-warning'; ?>">
                                                    <?php echo ucfirst($order['payment_status']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if (!empty($order['transaction_id'])): ?>
                                        <div class="mt-2">
                                            <small class="text-muted">Transaction ID:</small>
                                            <div class="small text-muted"><?php echo htmlspecialchars($order['transaction_id']); ?></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
