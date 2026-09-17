<?php
/**
 * Customer Login Page
 */

require_once 'includes/functions.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect(SITE_URL . '/user/index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    // Validation
    if (empty($email)) {
        $errors[] = 'Email is required';
    }
    if (empty($password)) {
        $errors[] = 'Password is required';
    }

    // Authenticate user if no errors
    if (empty($errors)) {
        $sql = "SELECT user_id, first_name, last_name, email, password, eco_points 
                FROM users 
                WHERE email = ?";
        $result = prepared_select($sql, "s", [$email]);
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (verify_password($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['eco_points'] = $user['eco_points'];

                // Update last login
                $update_sql = "UPDATE users SET last_login = NOW() WHERE user_id = ?";
                prepared_execute($update_sql, "i", [$user['user_id']]);

                set_flash_message('success', 'Welcome back, ' . htmlspecialchars($user['first_name']) . '!');
                
                // Redirect to dashboard or previous page
                $redirect = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : SITE_URL . '/user/index.php';
                unset($_SESSION['redirect_after_login']);
                redirect($redirect);
            } else {
                $errors[] = 'Invalid email or password';
            }
        } else {
            $errors[] = 'Invalid email or password';
        }
    }
}

$page_title = 'Login';
require_once 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="bi bi-box-arrow-in-right"></i> Login</h4>
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
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password *</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">Login</button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <p class="mb-0">Don't have an account? <a href="<?php echo SITE_URL; ?>/register.php">Register here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>