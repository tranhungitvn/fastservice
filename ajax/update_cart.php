<?php
require_once '../config/config.php';
require_once '../includes/Database.php';
require_once '../includes/Cart.php';

session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please login to update cart'
    ]);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['cart_id']) || !isset($input['quantity'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
    exit();
}

$cart = new Cart();
$result = $cart->updateQuantity($input['cart_id'], $input['quantity']);

if ($result) {
    // Get updated totals
    $cart_items = $cart->getCartItems($_SESSION['user_id']);
    $item_total = 0;
    foreach ($cart_items as $item) {
        if ($item['id'] == $input['cart_id']) {
            $item_total = $item['price'] * $item['quantity'];
            break;
        }
    }
    
    $cart_total = $cart->getCartTotal($_SESSION['user_id']);
    
    echo json_encode([
        'success' => true,
        'item_total' => $item_total,
        'cart_total' => $cart_total
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error updating cart'
    ]);
}