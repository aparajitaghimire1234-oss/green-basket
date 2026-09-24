<?php
/**
 * Admin Dashboard
 */

require_once '../includes/functions.php';
require_once '../includes/admin_auth.php';

$page_title = 'Admin Dashboard';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - GreenBasket Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
        .stat-card {
            border-radius: 10px;
            overflow: hidden;
        }
        .stat-card .icon {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
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
                    <a class="nav-link active" href="index.php">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                    <a class="nav-link" href="products.php">
                        <i class="bi bi-box-seam me-2"></i> Products
                    </a>
                    <a class="nav-link" href="categories.php">
                        <i class="bi bi-tags me-2"></i> Categories
                    </a>
                    <a class="nav-link" href="orders.php">
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
                    <h2>Dashboard</h2>
                    <div>
                        <span class="text-muted">Welcome, <?php echo htmlspecialchars($_SESSION['admin_full_name']); ?></span>
                    </div>
                </div>
                
                <?php
                // Get dashboard statistics
                $stats_sql = "SELECT 
                            (SELECT COUNT(*) FROM users) as total_users,
                            (SELECT COUNT(*) FROM products) as total_products,
                            (SELECT COUNT(*) FROM orders) as total_orders,
                            (SELECT COUNT(*) FROM orders WHERE order_status = 'pending') as pending_orders,
                            (SELECT COUNT(*) FROM orders WHERE order_status = 'delivered') as delivered_orders,
                            (SELECT SUM(final_amount) FROM orders WHERE order_status = 'delivered') as total_revenue,
                            (SELECT COUNT(*) FROM products WHERE stock_quantity < 10) as low_stock";
                $stats_result = prepared_select($stats_sql);
                $stats = $stats_result->fetch_assoc();
                
                // Get monthly sales
                $monthly_sales_sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(final_amount) as sales 
                                      FROM orders 
                                      WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                                      GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                                      ORDER BY month ASC";
                $monthly_sales_result = prepared_select($monthly_sales_sql);
                $monthly_sales = [];
                while ($row = $monthly_sales_result->fetch_assoc()) {
                    $monthly_sales[] = $row;
                }
                
                // Get recent orders
                $recent_orders_sql = "SELECT * FROM orders ORDER BY created_at DESC LIMIT 5";
                $recent_orders_result = prepared_select($recent_orders_sql);
                $recent_orders = [];
                while ($row = $recent_orders_result->fetch_assoc()) {
                    $recent_orders[] = $row;
                }
                
                // Get low stock products
                $low_stock_sql = "SELECT * FROM products WHERE stock_quantity < 10 ORDER BY stock_quantity ASC LIMIT 5";
                $low_stock_result = prepared_select($low_stock_sql);
                $low_stock = [];
                while ($row = $low_stock_result->fetch_assoc()) {
                    $low_stock[] = $row;
                }
                ?>
                
                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="icon bg-primary text-white rounded-circle me-3">
                                        <i class="bi bi-people"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Total Users</h6>
                                        <h3 class="mb-0"><?php echo $stats['total_users'] ?? 0; ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="icon bg-success text-white rounded-circle me-3">
                                        <i class="bi bi-box-seam"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Total Products</h6>
                                        <h3 class="mb-0"><?php echo $stats['total_products'] ?? 0; ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="icon bg-info text-white rounded-circle me-3">
                                        <i class="bi bi-bag"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Total Orders</h6>
                                        <h3 class="mb-0"><?php echo $stats['total_orders'] ?? 0; ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card stat-card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="icon bg-warning text-white rounded-circle me-3">
                                        <i class="bi bi-currency-rupee"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Revenue</h6>
                                        <h3 class="mb-0"><?php echo format_price($stats['total_revenue'] ?? 0); ?></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Secondary Stats -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="bg-warning text-white rounded p-3 me-3">
                                        <i class="bi bi-clock"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Pending Orders</h6>
                                        <h4 class="mb-0"><?php echo $stats['pending_orders'] ?? 0; ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="bg-success text-white rounded p-3 me-3">
                                        <i class="bi bi-check-circle"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Delivered Orders</h6>
                                        <h4 class="mb-0"><?php echo $stats['delivered_orders'] ?? 0; ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="bg-danger text-white rounded p-3 me-3">
                                        <i class="bi bi-exclamation-triangle"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Low Stock Products</h6>
                                        <h4 class="mb-0"><?php echo $stats['low_stock'] ?? 0; ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Recent Orders -->
                    <div class="col-md-6 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">Recent Orders</h5>
                                    <a href="orders.php" class="btn btn-sm btn-outline-success">View All</a>
                                </div>
                                
                                <?php if (empty($recent_orders)): ?>
                                    <p class="text-muted">No orders yet</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Order #</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recent_orders as $order): ?>
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
                                                        <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                                                        <td><?php echo format_price($order['final_amount']); ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $status_color; ?>">
                                                                <?php echo ucfirst($order['order_status']); ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Low Stock Alerts -->
                    <div class="col-md-6 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="mb-0">Low Stock Alerts</h5>
                                    <a href="products.php" class="btn btn-sm btn-outline-success">View All</a>
                                </div>
                                
                                <?php if (empty($low_stock)): ?>
                                    <p class="text-muted">All products are well stocked</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Product</th>
                                                    <th>Stock</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($low_stock as $product): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                                        <td>
                                                            <span class="badge bg-danger"><?php echo $product['stock_quantity']; ?></span>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Monthly Sales Chart -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="mb-4">Monthly Sales (Last 6 Months)</h5>
                        <canvas id="salesChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Sales Chart
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($monthly_sales, 'month')); ?>,
                datasets: [{
                    label: 'Sales (Rs)',
                    data: <?php echo json_encode(array_column($monthly_sales, 'sales')); ?>,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
