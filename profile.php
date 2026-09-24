<?php
/**
 * Customer Profile Page
 */

require_once '../includes/functions.php';
require_once '../includes/auth.php';

$page_title = 'My Profile';
require_once '../includes/header.php';

$user_id = $_SESSION['user_id'];
$user = get_user($user_id);

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = sanitize_input($_POST['first_name'] ?? '');
    $last_name = sanitize_input($_POST['last_name'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $address = sanitize_input($_POST['address'] ?? '');
    $city = sanitize_input($_POST['city'] ?? '');
    $state = sanitize_input($_POST['state'] ?? '');
    $postal_code = sanitize_input($_POST['postal_code'] ?? '');

    // Validation
    if (empty($first_name)) {
        $errors[] = 'First name is required';
    }
    if (empty($last_name)) {
        $errors[] = 'Last name is required';
    }
    if (!empty($phone) && !validate_phone($phone)) {
        $errors[] = 'Invalid phone number';
    }

    // Update profile if no errors
    if (empty($errors)) {
        $sql = "UPDATE users SET first_name = ?, last_name = ?, phone = ?, address = ?, city = ?, state = ?, postal_code = ? WHERE user_id = ?";
        
        if (prepared_execute($sql, "sssssssi", [
            $first_name, $last_name, $phone, $address, $city, $state, $postal_code, $user_id
        ])) {
            $success = true;
            set_flash_message('success', 'Profile updated successfully');
            
            // Update session
            $_SESSION['first_name'] = $first_name;
            $_SESSION['last_name'] = $last_name;
            
            // Refresh user data
            $user = get_user($user_id);
        } else {
            $errors[] = 'Failed to update profile. Please try again.';
        }
    }
}
?>

<!-- Page Header -->
<section class="bg-success text-white py-4">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="fw-bold">My Profile</h1>
                <p class="mb-0">Manage your account settings</p>
            </div>
        </div>
    </div>
</section>

<!-- Profile Content -->
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
                            <a href="<?php echo SITE_URL; ?>/user/index.php" class="list-group-item list-group-item-action">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard
                            </a>
                            <a href="<?php echo SITE_URL; ?>/user/profile.php" class="list-group-item list-group-item-action active">
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
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="mb-4">Personal Information</h5>
                        
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
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">First Name *</label>
                                    <input type="text" class="form-control" name="first_name" 
                                           value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Last Name *</label>
                                    <input type="text" class="form-control" name="last_name" 
                                           value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                                <small class="text-muted">Email cannot be changed. Contact support if needed.</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" name="phone" 
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" 
                                       placeholder="10-digit mobile number">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">City</label>
                                    <input type="text" class="form-control" name="city" 
                                           value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">State</label>
                                    <input type="text" class="form-control" name="state" 
                                           value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Postal Code</label>
                                    <input type="text" class="form-control" name="postal_code" 
                                           value="<?php echo htmlspecialchars($user['postal_code'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Country</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['country']); ?>" readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Member Since</label>
                                <input type="text" class="form-control" value="<?php echo format_date($user['created_at']); ?>" readonly>
                            </div>
                            
                            <button type="submit" class="btn btn-success">Update Profile</button>
                        </form>
                    </div>
                </div>
                
                <!-- Eco Points History -->
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body p-4">
                        <h5 class="mb-4">Eco Points History</h5>
                        
                        <?php
                        $eco_history_sql = "SELECT * FROM eco_points WHERE user_id = ? ORDER BY created_at DESC LIMIT 10";
                        $eco_history_result = prepared_select($eco_history_sql, "i", [$user_id]);
                        $eco_history = [];
                        while ($row = $eco_history_result->fetch_assoc()) {
                            $eco_history[] = $row;
                        }
                        ?>
                        
                        <?php if (empty($eco_history)): ?>
                            <p class="text-muted">No eco points history yet. Start shopping to earn points!</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Description</th>
                                            <th>Points</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($eco_history as $point): ?>
                                            <tr>
                                                <td><?php echo format_date($point['created_at']); ?></td>
                                                <td><?php echo htmlspecialchars($point['description']); ?></td>
                                                <td class="text-success fw-bold">+<?php echo $point['points']; ?></td>
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
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>
