<?php
/**
 * Customer Dashboard
 */

require_once '../includes/functions.php';
require_once '../includes/auth.php';

$page_title = 'My Dashboard';
require_once '../includes/header.php';

$user_id = $_SESSION['user_id'];
$user = get_user($user_id);

// Get order statistics
$order_stats_sql = "SELECT 
                    COUNT(*) as total_orders,
                    SUM(CASE WHEN order_status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
                    SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
                    SUM(final_amount) as total_spent
                    FROM orders WHERE user_id = ?";
$order_stats_result = prepared_select($order_stats_sql, "i", [$user_id]);
$order_stats = $order_stats_result->fetch_assoc();

// Get recent orders
$recent_orders_sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
$recent_orders_result = prepared_select($recent_orders_sql, "i", [$user_id]);
$recent_orders = [];
while ($row = $recent_orders_result->fetch_assoc()) {
    $recent_orders[] = $row;
}
?>

<!-- Page Header -->
<section class="bg-success text-white py-4">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="fw-bold">My Dashboard</h1>
                <p class="mb-0">Welcome back, <?php echo htmlspecialchars($user['first_name']); ?>!</p>
            </div>
        </div>
    </div>
</section>

<!-- Dashboard Content -->
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
                        <h5><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h5>
                        <p class="text-muted mb-3"><?php echo htmlspecialchars($user['email']); ?></p>
                        <div class="alert alert-success">
                            <i class="bi bi-currency-rupee me-1"></i>
                            <strong><?php echo $user['eco_points']; ?></strong> Eco Points
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <a href="<?php echo SITE_URL; ?>/user/index.php" class="list-group-item list-group-item-action active">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard
                            </a>
                            <a href="<?php echo SITE_URL; ?>/user/profile.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-person-gear me-2"></i> Profile
                            </a>
                            <a href="<?php echo SITE_URL; ?>/user/orders.php" class="list-group-item list-group-item-action">
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
                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                        <i class="bi bi-bag"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Total Orders</h6>
                                        <h4 class="mb-0"><?php echo $order_stats['total_orders'] ?? 0; ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                        <i class="bi bi-clock"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Pending</h6>
                                        <h4 class="mb-0"><?php echo $order_stats['pending_orders'] ?? 0; ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="bg-info text-white rounded-circle d-inline-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                        <i class="bi bi-check-circle"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Delivered</h6>
                                        <h4 class="mb-0"><?php echo $order_stats['delivered_orders'] ?? 0; ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                                        <i class="bi bi-currency-rupee"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Total Spent</h6>
                                        <h4 class="mb-0"><?php echo format_price($order_stats['total_spent'] ?? 0); ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Orders -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="mb-0">Recent Orders</h5>
                            <a href="<?php echo SITE_URL; ?>/user/orders.php" class="btn btn-sm btn-outline-success">View All</a>
                        </div>
                        
                        <?php if (empty($recent_orders)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-bag-x" style="font-size: 60px; color: #ccc;"></i>
                                <p class="text-muted mt-3">No orders yet</p>
                                <a href="<?php echo SITE_URL; ?>/shop.php" class="btn btn-success">Start Shopping</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Order #</th>
                                            <th>Date</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_orders as $order): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                                                <td><?php echo format_date($order['created_at']); ?></td>
                                                <td><?php echo format_price($order['final_amount']); ?></td>
                                                <td>
                                                    <?php
                                                    $status_colors = [
                                                        'pending' => 'warning',
                                                        'processing' => 'info',
                                                        'shipped' => 'primary',
                                                        'delivered' => 'success',
                                                        'cancelled' => 'danger'
                                                    ];
                                                    $status_color = $status_colors[$order['order_status']] ?? 'secondary';
                                                    ?>
                                                    <span class="badge bg-<?php echo $status_color; ?>">
                                                        <?php echo ucfirst($order['order_status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="<?php echo SITE_URL; ?>/user/order-details.php?id=<?php echo $order['order_id']; ?>" 
                                                       class="btn btn-sm btn-outline-secondary">View</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="row mt-4">
                    <div class="col-md-4 mb-3">
                        <a href="<?php echo SITE_URL; ?>/shop.php" class="card border-0 shadow-sm text-decoration-none">
                            <div class="card-body text-center">
                                <i class="bi bi-cart-plus" style="font-size: 40px; color: var(--primary-green);"></i>
                                <h6 class="mt-3">Shop Now</h6>
                                <p class="small text-muted">Browse our eco-friendly products</p>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <a href="<?php echo SITE_URL; ?>/user/wishlist.php" class="card border-0 shadow-sm text-decoration-none">
                            <div class="card-body text-center">
                                <i class="bi bi-heart" style="font-size: 40px; color: var(--primary-green);"></i>
                                <h6 class="mt-3">Wishlist</h6>
                                <p class="small text-muted">View your saved products</p>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <a href="<?php echo SITE_URL; ?>/eco-tips.php" class="card border-0 shadow-sm text-decoration-none">
                            <div class="card-body text-center">
                                <i class="bi bi-lightbulb" style="font-size: 40px; color: var(--primary-green);"></i>
                                <h6 class="mt-3">Eco Tips</h6>
                                <p class="small text-muted">Learn sustainable living tips</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
