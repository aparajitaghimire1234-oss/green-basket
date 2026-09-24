<?php
/**
 * API: Add to Cart
 */

require_once '../includes/functions.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = intval($_POST['product_id'] ?? 0);
$quantity = intval($_POST['quantity'] ?? 1);

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit;
}

if ($quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid quantity']);
    exit;
}

// Check if product exists and is active
$product = get_product($product_id);
if (!$product || !$product['is_active']) {
    echo json_encode(['success' => false, 'message' => 'Product not available']);
    exit;
}

// Check stock
if ($product['stock_quantity'] < $quantity) {
    echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
    exit;
}

// Check if item already in cart
$sql = "SELECT cart_id, quantity FROM cart WHERE user_id = ? AND product_id = ?";
$result = prepared_select($sql, "ii", [$user_id, $product_id]);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $new_quantity = $row['quantity'] + $quantity;
    
    // Check stock again
    if ($product['stock_quantity'] < $new_quantity) {
        echo json_encode(['success' => false, 'message' => 'Insufficient stock for requested quantity']);
        exit;
    }
    
    // Update quantity
    $update_sql = "UPDATE cart SET quantity = ? WHERE cart_id = ?";
    if (prepared_execute($update_sql, "ii", [$new_quantity, $row['cart_id']])) {
        echo json_encode(['success' => true, 'message' => 'Cart updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update cart']);
    }
} else {
    // Add new item
    $insert_sql = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
    if (prepared_execute($insert_sql, "iii", [$user_id, $product_id, $quantity])) {
        echo json_encode(['success' => true, 'message' => 'Product added to cart']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add product to cart']);
    }
}
