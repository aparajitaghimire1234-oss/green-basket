<?php
/**
 * Customer Registration Page
 */

require_once 'includes/functions.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect(SITE_URL . '/user/index.php');
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $first_name = sanitize_input($_POST['first_name'] ?? '');
    $last_name = sanitize_input($_POST['last_name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
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
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!validate_email($email)) {
        $errors[] = 'Invalid email format';
    }
    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    }
    if (!empty($phone) && !validate_phone($phone)) {
        $errors[] = 'Invalid phone number';
    }

    // Check if email already exists
    if (empty($errors)) {
        $sql = "SELECT user_id FROM users WHERE email = ?";
        $result = prepared_select($sql, "s", [$email]);
        if ($result->num_rows > 0) {
            $errors[] = 'Email already registered';
        }
    }

    // Register user if no errors
    if (empty($errors)) {
        $hashed_password = hash_password($password);
        
        $sql = "INSERT INTO users (first_name, last_name, email, password, phone, address, city, state, postal_code) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        if (prepared_execute($sql, "sssssssss", [
            $first_name, $last_name, $email, $hashed_password, 
            $phone, $address, $city, $state, $postal_code
        ])) {
            $success = true;
            set_flash_message('success', 'Registration successful! Please login.');
            redirect(SITE_URL . '/login.php');
        } else {
            $errors[] = 'Registration failed. Please try again.';
        }
    }
}

$page_title = 'Register';
require_once 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="bi bi-person-plus"></i> Create Account</h4>
                </div>
                <div class="card-body">
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
                                <label for="first_name" class="form-label">First Name *</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($first_name ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name *</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($last_name ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <small class="text-muted">Minimum 6 characters</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($phone ?? ''); ?>" 
                                   placeholder="10-digit mobile number">
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="2"><?php echo htmlspecialchars($address ?? ''); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" 
                                       value="<?php echo htmlspecialchars($city ?? ''); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="state" class="form-label">State</label>
                                <input type="text" class="form-control" id="state" name="state" 
                                       value="<?php echo htmlspecialchars($state ?? ''); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="postal_code" class="form-label">Postal Code</label>
                                <input type="text" class="form-control" id="postal_code" name="postal_code" 
                                       value="<?php echo htmlspecialchars($postal_code ?? ''); ?>">
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">Register</button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <p class="mb-0">Already have an account? <a href="<?php echo SITE_URL; ?>/login.php">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
