<?php
require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/Cart.php';

session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please login to add items to cart'
    ]);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['product_id']) || !isset($input['quantity'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
    exit();
}

$cart = new Cart();
$result = $cart->addItem($_SESSION['user_id'], $input['product_id'], $input['quantity']);

if ($result) {
    $cart_count = $cart->getCartCount($_SESSION['user_id']);
    echo json_encode([
        'success' => true,
        'message' => 'Product added to cart successfully',
        'cart_count' => $cart_count
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error adding product to cart'
    ]);
}
