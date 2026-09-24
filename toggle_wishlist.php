<?php
/**
 * API: Toggle Wishlist
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

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit;
}

// Check if product exists
$product = get_product($product_id);
if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

// Check if item already in wishlist
$sql = "SELECT wishlist_id FROM wishlist WHERE user_id = ? AND product_id = ?";
$result = prepared_select($sql, "ii", [$user_id, $product_id]);

if ($result->num_rows > 0) {
    // Remove from wishlist
    $delete_sql = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
    if (prepared_execute($delete_sql, "ii", [$user_id, $product_id])) {
        echo json_encode(['success' => true, 'message' => 'Removed from wishlist']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to remove from wishlist']);
    }
} else {
    // Add to wishlist
    $insert_sql = "INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)";
    if (prepared_execute($insert_sql, "ii", [$user_id, $product_id])) {
        echo json_encode(['success' => true, 'message' => 'Added to wishlist']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add to wishlist']);
    }
}
