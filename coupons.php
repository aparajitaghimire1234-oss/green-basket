<?php
/**
 * Admin Coupons Management
 */

require_once '../includes/functions.php';
require_once '../includes/admin_auth.php';

$page_title = 'Manage Coupons';

// Handle coupon actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = sanitize_input($_POST['action'] ?? '');
    
    if ($action === 'add' || $action === 'edit') {
        $coupon_code = strtoupper(sanitize_input($_POST['coupon_code'] ?? ''));
        $description = sanitize_input($_POST['description'] ?? '');
        $discount_type = sanitize_input($_POST['discount_type'] ?? 'percentage');
        $discount_value = floatval($_POST['discount_value'] ?? 0);
        $min_purchase = floatval($_POST['min_purchase'] ?? 0);
        $max_discount = floatval($_POST['max_discount'] ?? 0);
        $usage_limit = intval($_POST['usage_limit'] ?? 0);
        $valid_until = sanitize_input($_POST['valid_until'] ?? '');
        
        $errors = [];
        if (empty($coupon_code)) $errors[] = 'Coupon code is required';
        if ($discount_value <= 0) $errors[] = 'Discount value is required';
        if (empty($valid_until)) $errors[] = 'Valid until date is required';
        
        if (empty($errors)) {
            if ($action === 'add') {
                $sql = "INSERT INTO coupons (coupon_code, description, discount_type, discount_value, min_purchase, max_discount, usage_limit, valid_until) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                prepared_execute($sql, "sssdidis", [
                    $coupon_code, $description, $discount_type, $discount_value, 
                    $min_purchase, $max_discount, $usage_limit, $valid_until
                ]);
                set_flash_message('success', 'Coupon added successfully');
            } else {
                $coupon_id = intval($_POST['coupon_id'] ?? 0);
                $sql = "UPDATE coupons SET coupon_code = ?, description = ?, discount_type = ?, discount_value = ?, 
                        min_purchase = ?, max_discount = ?, usage_limit = ?, valid_until = ? WHERE coupon_id = ?";
                prepared_execute($sql, "sssdidisi", [
                    $coupon_code, $description, $discount_type, $discount_value, 
                    $min_purchase, $max_discount, $usage_limit, $valid_until, $coupon_id
                ]);
                set_flash_message('success', 'Coupon updated successfully');
            }
            redirect(SITE_URL . '/admin/coupons.php');
        }
    }
    
    if ($action === 'delete') {
        $coupon_id = intval($_POST['coupon_id'] ?? 0);
        if ($coupon_id > 0) {
            $sql = "DELETE FROM coupons WHERE coupon_id = ?";
            prepared_execute($sql, "i", [$coupon_id]);
            set_flash_message('success', 'Coupon deleted successfully');
        }
        redirect(SITE_URL . '/admin/coupons.php');
    }
}

// Get coupons
$sql = "SELECT * FROM coupons ORDER BY created_at DESC";
$result = prepared_select($sql);
$coupons = [];
while ($row = $result->fetch_assoc()) {
    $coupons[] = $row;
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
                    <a class="nav-link" href="orders.php">
                        <i class="bi bi-bag me-2"></i> Orders
                    </a>
                    <a class="nav-link" href="customers.php">
                        <i class="bi bi-people me-2"></i> Customers
                    </a>
                    <a class="nav-link" href="reviews.php">
                        <i class="bi bi-star me-2"></i> Reviews
                    </a>
                    <a class="nav-link active" href="coupons.php">
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
                    <h2>Manage Coupons</h2>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#couponModal">
                        <i class="bi bi-plus-circle"></i> Add Coupon
                    </button>
                </div>
                
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Description</th>
                                        <th>Discount</th>
                                        <th>Min Purchase</th>
                                        <th>Usage</th>
                                        <th>Valid Until</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($coupons as $coupon): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($coupon['coupon_code']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($coupon['description']); ?></td>
                                            <td>
                                                <?php if ($coupon['discount_type'] == 'percentage'): ?>
                                                    <?php echo $coupon['discount_value']; ?>%
                                                <?php else: ?>
                                                    <?php echo format_price($coupon['discount_value']); ?>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo format_price($coupon['min_purchase']); ?></td>
                                            <td><?php echo $coupon['used_count']; ?> / <?php echo $coupon['usage_limit'] ?: '∞'; ?></td>
                                            <td><?php echo format_date($coupon['valid_until']); ?></td>
                                            <td>
                                                <span class="badge <?php echo $coupon['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                                    <?php echo $coupon['is_active'] ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteCoupon(<?php echo $coupon['coupon_id']; ?>)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
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
    
    <!-- Coupon Modal -->
    <div class="modal fade" id="couponModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Coupon</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="mb-3">
                            <label class="form-label">Coupon Code *</label>
                            <input type="text" class="form-control" name="coupon_code" placeholder="e.g., ECO10" required style="text-transform: uppercase;">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <input type="text" class="form-control" name="description">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Discount Type *</label>
                                <select class="form-select" name="discount_type">
                                    <option value="percentage">Percentage (%)</option>
                                    <option value="fixed">Fixed Amount (Rs)</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Discount Value *</label>
                                <input type="number" class="form-control" name="discount_value" step="0.01" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Minimum Purchase</label>
                                <input type="number" class="form-control" name="min_purchase" step="0.01" value="0">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Max Discount</label>
                                <input type="number" class="form-control" name="max_discount" step="0.01">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Usage Limit</label>
                            <input type="number" class="form-control" name="usage_limit" placeholder="Leave empty for unlimited">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Valid Until *</label>
                            <input type="date" class="form-control" name="valid_until" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Save Coupon</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function deleteCoupon(couponId) {
            if (confirm('Are you sure you want to delete this coupon?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete';
                
                const couponIdInput = document.createElement('input');
                couponIdInput.type = 'hidden';
                couponIdInput.name = 'coupon_id';
                couponIdInput.value = couponId;
                
                form.appendChild(actionInput);
                form.appendChild(couponIdInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
