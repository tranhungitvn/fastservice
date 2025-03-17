<?php
require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/Cart.php';

session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please login to remove items from cart'
    ]);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['cart_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
    exit();
}

$cart = new Cart();
$result = $cart->removeItem($input['cart_id']);

if ($result) {
    $cart_total = $cart->getCartTotal($_SESSION['user_id']);
    $cart_count = $cart->getCartCount($_SESSION['user_id']);
    
    echo json_encode([
        'success' => true,
        'cart_total' => $cart_total,
        'cart_count' => $cart_count
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error removing item from cart'
    ]);
}