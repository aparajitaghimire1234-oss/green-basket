<?php
/**
 * Khalti Payment Initiation Page
 * Initiates Khalti ePayment and redirects to Khalti payment page
 */

require_once 'includes/functions.php';
require_once 'includes/auth.php';
require_once 'includes/khalti.php';

// Check if pending order exists in session
if (!isset($_SESSION['pending_order'])) {
    set_flash_message('danger', 'No pending order found. Please try again.');
    redirect(SITE_URL . '/cart.php');
}

$pending_order = $_SESSION['pending_order'];
$user_id = $_SESSION['user_id'];

// Get user details
$user = get_user($user_id);

// Validate phone number for Khalti
if (!KhaltiPayment::validatePhone($pending_order['phone'])) {
    set_flash_message('danger', 'Invalid phone number for Khalti payment. Please use a valid 10-digit mobile number starting with 9.');
    redirect(SITE_URL . '/checkout.php');
}

// Generate order number for reference
$order_number = generate_order_number();

// Prepare customer information
$customer_name = $user['first_name'] . ' ' . $user['last_name'];
$customer_email = $user['email'];
$customer_phone = $pending_order['phone'];

// Initiate Khalti payment
$payment_result = KhaltiPayment::initiatePayment(
    $pending_order['final_amount'],
    $order_number,
    "Order #$order_number - GreenBasket",
    $customer_name,
    $customer_email,
    $customer_phone
);

if ($payment_result['success']) {
    // Store order number and payment URL in session
    $_SESSION['pending_order']['order_number'] = $order_number;
    $_SESSION['pending_order']['payment_url'] = $payment_result['payment_url'];
    $_SESSION['pending_order']['pidx'] = $payment_result['pidx'] ?? null;
    
    // Redirect to Khalti payment page
    header('Location: ' . $payment_result['payment_url']);
    exit();
} else {
    // Payment initiation failed
    set_flash_message('danger', KHALTI_ERROR_INITIATION . ' Error: ' . $payment_result['error']);
    redirect(SITE_URL . '/checkout.php');
}
