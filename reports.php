<?php
/**
 * Admin Reports Page
 */

require_once '../includes/functions.php';
require_once '../includes/admin_auth.php';

$page_title = 'Reports & Analytics';

// Get report data
$monthly_sales_sql = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as orders, SUM(final_amount) as revenue 
                      FROM orders 
                      WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                      GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                      ORDER BY month ASC";
$monthly_sales_result = prepared_select($monthly_sales_sql);
$monthly_sales = [];
while ($row = $monthly_sales_result->fetch_assoc()) {
    $monthly_sales[] = $row;
}

// Top selling products
$top_products_sql = "SELECT p.product_name, SUM(oi.quantity) as total_sold, SUM(oi.subtotal) as revenue 
                      FROM order_items oi 
                      LEFT JOIN products p ON oi.product_id = p.product_id 
                      GROUP BY oi.product_id 
                      ORDER BY total_sold DESC 
                      LIMIT 10";
$top_products_result = prepared_select($top_products_sql);
$top_products = [];
while ($row = $top_products_result->fetch_assoc()) {
    $top_products[] = $row;
}

// Category sales
$category_sales_sql = "SELECT c.category_name, COUNT(DISTINCT o.order_id) as orders, SUM(oi.subtotal) as revenue 
                        FROM order_items oi 
                        LEFT JOIN products p ON oi.product_id = p.product_id 
                        LEFT JOIN categories c ON p.category_id = c.category_id 
                        LEFT JOIN orders o ON oi.order_id = o.order_id 
                        GROUP BY c.category_id 
                        ORDER BY revenue DESC";
$category_sales_result = prepared_select($category_sales_sql);
$category_sales = [];
while ($row = $category_sales_result->fetch_assoc()) {
    $category_sales[] = $row;
}

// Customer statistics
$customer_stats_sql = "SELECT 
                       (SELECT COUNT(*) FROM users) as total_customers,
                       (SELECT COUNT(DISTINCT user_id) FROM orders) as active_customers,
                       (SELECT AVG(final_amount) FROM orders) as avg_order_value";
$customer_stats_result = prepared_select($customer_stats_sql);
$customer_stats = $customer_stats_result->fetch_assoc();
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
                    <a class="nav-link active" href="reports.php">
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
                
                <h2 class="mb-4">Reports & Analytics</h2>
                
                <!-- Customer Statistics -->
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="mb-2">Total Customers</h6>
                                <h3 class="mb-0"><?php echo $customer_stats['total_customers'] ?? 0; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="mb-2">Active Customers</h6>
                                <h3 class="mb-0"><?php echo $customer_stats['active_customers'] ?? 0; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="mb-2">Avg Order Value</h6>
                                <h3 class="mb-0"><?php echo format_price($customer_stats['avg_order_value'] ?? 0); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Monthly Sales Chart -->
                    <div class="col-md-8 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h5 class="mb-4">Monthly Sales (Last 12 Months)</h5>
                                <canvas id="salesChart" height="120"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Category Sales -->
                    <div class="col-md-4 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h5 class="mb-4">Sales by Category</h5>
                                <canvas id="categoryChart" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Top Selling Products -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="mb-4">Top Selling Products</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Units Sold</th>
                                        <th>Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($top_products as $product): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                            <td><?php echo $product['total_sold']; ?></td>
                                            <td><?php echo format_price($product['revenue']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Category Sales Table -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="mb-4">Category Performance</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Orders</th>
                                        <th>Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($category_sales as $category): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                                            <td><?php echo $category['orders']; ?></td>
                                            <td><?php echo format_price($category['revenue']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Monthly Sales Chart
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($monthly_sales, 'month')); ?>,
                datasets: [{
                    label: 'Revenue (Rs)',
                    data: <?php echo json_encode(array_column($monthly_sales, 'revenue')); ?>,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Orders',
                    data: <?php echo json_encode(array_column($monthly_sales, 'orders')); ?>,
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        position: 'left'
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                }
            }
        });
        
        // Category Sales Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($category_sales, 'category_name')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($category_sales, 'revenue')); ?>,
                    backgroundColor: [
                        '#28a745',
                        '#17a2b8',
                        '#ffc107',
                        '#dc3545',
                        '#6c757d',
                        '#6610f2',
                        '#fd7e14'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>