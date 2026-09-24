<?php
/**
 * Admin Orders Management
 */

require_once '../includes/functions.php';
require_once '../includes/admin_auth.php';

$page_title = 'Manage Orders';

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id = intval($_POST['order_id'] ?? 0);
    $order_status = sanitize_input($_POST['order_status'] ?? '');

    if ($order_id > 0 && in_array($order_status, ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])) {
        if ($order_status === 'delivered') {
            $sql = "UPDATE orders SET order_status = ?, 
                    payment_status = CASE WHEN payment_method = 'COD' THEN 'paid' ELSE payment_status END 
                    WHERE order_id = ?";
        } else {
            $sql = "UPDATE orders SET order_status = ? WHERE order_id = ?";
        }
        prepared_execute($sql, "si", [$order_status, $order_id]);
        set_flash_message('success', 'Order status updated successfully');
    }
    redirect(SITE_URL . '/admin/orders.php');
}

// Get orders with pagination
$page = intval($_GET['page'] ?? 1);
$per_page = ADMIN_PRODUCTS_PER_PAGE;
$offset = ($page - 1) * $per_page;

$count_sql = "SELECT COUNT(*) as total FROM orders";
$count_result = prepared_select($count_sql);
$total_row = $count_result->fetch_assoc();
$total_orders = $total_row['total'];
$total_pages = ceil($total_orders / $per_page);

$sql = "SELECT o.*, u.first_name, u.last_name FROM orders o LEFT JOIN users u ON o.user_id = u.user_id ORDER BY o.created_at DESC LIMIT ? OFFSET ?";
$result = prepared_select($sql, "ii", [$per_page, $offset]);
$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
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
                    <a class="nav-link" href="products.php">
                        <i class="bi bi-box-seam me-2"></i> Products
                    </a>
                    <a class="nav-link" href="categories.php">
                        <i class="bi bi-tags me-2"></i> Categories
                    </a>
                    <a class="nav-link active" href="orders.php">
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
                    <h2>Manage Orders</h2>
                </div>
                
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Date</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Payment</th>
                                        <th>Transaction ID</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
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
                                        ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
                                            <td><?php echo format_date($order['created_at']); ?></td>
                                            <td><?php echo format_price($order['final_amount']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $status_color; ?>">
                                                    <?php echo ucfirst($order['order_status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $order['payment_status'] == 'paid' ? 'bg-success' : 'bg-warning'; ?>">
                                                    <?php echo ucfirst($order['payment_status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo !empty($order['transaction_id']) ? htmlspecialchars($order['transaction_id']) : '-'; ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                                        <i class="bi bi-three-dots"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="#" onclick="updateStatus(<?php echo $order['order_id']; ?>, 'pending')">Pending</a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="updateStatus(<?php echo $order['order_id']; ?>, 'processing')">Processing</a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="updateStatus(<?php echo $order['order_id']; ?>, 'shipped')">Shipped</a></li>
                                                        <li><a class="dropdown-item" href="#" onclick="updateStatus(<?php echo $order['order_id']; ?>, 'delivered')">Delivered</a></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><a class="dropdown-item text-danger" href="#" onclick="updateStatus(<?php echo $order['order_id']; ?>, 'cancelled')">Cancel</a></li>
                                                    </ul>
                                                </div>
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateStatus(orderId, status) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'update_status';
            
            const orderIdInput = document.createElement('input');
            orderIdInput.type = 'hidden';
            orderIdInput.name = 'order_id';
            orderIdInput.value = orderId;
            
            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'order_status';
            statusInput.value = status;
            
            form.appendChild(actionInput);
            form.appendChild(orderIdInput);
            form.appendChild(statusInput);
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html>
