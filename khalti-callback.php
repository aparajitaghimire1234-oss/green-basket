<?php
/**
 * Khalti Payment Callback Handler
 * Handles payment completion/verification from Khalti
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

// Get callback parameters from Khalti
$pidx = $_GET['pidx'] ?? null;
$amount = $_GET['amount'] ?? null;
$purchase_order_id = $_GET['purchase_order_id'] ?? null;
$status = $_GET['status'] ?? null;

// Check if payment was cancelled by user
if ($status === 'Cancelled' || $status === 'cancelled' || empty($pidx)) {
    set_flash_message('warning', KHALTI_ERROR_CANCELLED);
    unset($_SESSION['pending_order']);
    redirect(SITE_URL . '/cart.php');
}

// Verify payment with Khalti API
$verification_result = KhaltiPayment::verifyPayment($pidx);

if ($verification_result['success']) {
    // Payment verified successfully
    $transaction_id = $verification_result['transaction_id'];
    $paid_amount = $verification_result['amount'];
    
    // Verify amount matches (allow small difference due to rounding)
    $amount_diff = abs($paid_amount - $pending_order['final_amount']);
    if ($amount_diff > 1) { // Allow 1 NPR difference
        set_flash_message('danger', 'Payment amount mismatch. Please contact support.');
        unset($_SESSION['pending_order']);
        redirect(SITE_URL . '/cart.php');
    }
    
    // Generate order number
    $order_number = $pending_order['order_number'] ?? generate_order_number();
    
    // Insert order with payment details
    $order_sql = "INSERT INTO orders (user_id, order_number, total_amount, discount_amount, final_amount, 
                  shipping_address, city, state, postal_code, phone, order_status, payment_method, 
                  payment_status, transaction_id, notes, eco_points_earned) 
                  VALUES (?, ?, ?, 0, ?, ?, ?, ?, ?, ?, 'pending', ?, 'paid', ?, ?, ?)";
    
    if (prepared_execute($order_sql, "isddsssssssi", [
        $user_id, $order_number, $pending_order['total_amount'], $pending_order['final_amount'], 
        $pending_order['shipping_address'], $pending_order['city'], $pending_order['state'], 
        $pending_order['postal_code'], $pending_order['phone'], 
        $pending_order['payment_method'], $transaction_id, $pending_order['notes'], 
        $pending_order['eco_points_earned']
    ])) {
        $order_id = get_last_insert_id();

        // Insert order items
        foreach ($pending_order['cart_items'] as $item) {
            $item_sql = "INSERT INTO order_items (order_id, product_id, product_name, quantity, price, subtotal) 
                        VALUES (?, ?, ?, ?, ?, ?)";
            prepared_execute($item_sql, "iisidd", [
                $order_id, $item['product_id'], $item['product_name'], 
                $item['quantity'], $item['price'], $item['subtotal']
            ]);

            // Update product stock
            $stock_sql = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?";
            prepared_execute($stock_sql, "ii", [$item['quantity'], $item['product_id']]);
        }

        // Add eco points to user
        $points_sql = "UPDATE users SET eco_points = eco_points + ? WHERE user_id = ?";
        prepared_execute($points_sql, "ii", [$pending_order['eco_points_earned'], $user_id]);

        // Log eco points
        $eco_log_sql = "INSERT INTO eco_points (user_id, points, description, order_id) VALUES (?, ?, ?, ?)";
        prepared_execute($eco_log_sql, "iisi", [
            $user_id, $pending_order['eco_points_earned'], "Order #$order_number", $order_id
        ]);

        // Clear cart
        $clear_cart_sql = "DELETE FROM cart WHERE user_id = ?";
        prepared_execute($clear_cart_sql, "i", [$user_id]);

        // Update session
        $_SESSION['eco_points'] += $pending_order['eco_points_earned'];

        // Clear pending order from session
        unset($_SESSION['pending_order']);

        set_flash_message('success', "Payment successful! Order #$order_number has been placed.");
        redirect(SITE_URL . '/user/orders.php');
    } else {
        // Failed to create order
        set_flash_message('danger', 'Payment verified but failed to create order. Please contact support with Transaction ID: ' . $transaction_id);
        unset($_SESSION['pending_order']);
        redirect(SITE_URL . '/cart.php');
    }
} else {
    // Payment verification failed
    $error_message = $verification_result['error'] ?? 'Unknown error';
    set_flash_message('danger', KHALTI_ERROR_VERIFICATION . ' ' . $error_message);
    unset($_SESSION['pending_order']);
    redirect(SITE_URL . '/cart.php');
}
