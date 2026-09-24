<?php
/**
 * API: Add Product Review
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
$rating = intval($_POST['rating'] ?? 0);
$review_text = sanitize_input($_POST['review_text'] ?? '');

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit;
}

if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Invalid rating']);
    exit;
}

if (empty($review_text)) {
    echo json_encode(['success' => false, 'message' => 'Review text is required']);
    exit;
}

// Check if product exists
$product = get_product($product_id);
if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    exit;
}

// Check if user already reviewed
$sql = "SELECT review_id FROM reviews WHERE user_id = ? AND product_id = ?";
$result = prepared_select($sql, "ii", [$user_id, $product_id]);

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'You have already reviewed this product']);
    exit;
}

// Add review
$sql = "INSERT INTO reviews (user_id, product_id, rating, review_text) VALUES (?, ?, ?, ?)";
if (prepared_execute($sql, "iiis", [$user_id, $product_id, $rating, $review_text])) {
    echo json_encode(['success' => true, 'message' => 'Review submitted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to submit review']);
}
