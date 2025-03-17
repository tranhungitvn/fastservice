<?php
require_once 'config/config.php';
require_once 'includes/Database.php';
require_once 'includes/Cart.php';
require_once 'includes/Order.php';
require_once 'includes/User.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=checkout');
    exit();
}

$cart = new Cart();
$user = new User();
$cart_items = $cart->getCartItems($_SESSION['user_id']);
$cart_total = $cart->getCartTotal($_SESSION['user_id']);
$user_info = $user->getCurrentUser();

if (empty($cart_items)) {
    header('Location: cart.php');
    exit();
}

// Handle checkout submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order = new Order();
    
    $shipping_address = [
        'full_name' => $_POST['full_name'],
        'address' => $_POST['address'],
        'city' => $_POST['city'],
        'state' => $_POST['state'],
        'postal_code' => $_POST['postal_code'],
        'country' => $_POST['country'],
        'phone' => $_POST['phone']
    ];

    $result = $order->createOrder($_SESSION['user_id'], $cart_items, $shipping_address);

    if ($result['success']) {
        // Clear the cart
        $cart->clearCart($_SESSION['user_id']);
        
        // Redirect to order confirmation
        header("Location: order-confirmation.php?order_id=" . $result['order_id']);
        exit();
    } else {
        $error = $result['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .checkout-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }

        .checkout-form {
            background: #fff;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .order-summary {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 8px;
            position: sticky;
            top: 2rem;
            height: fit-content;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .order-items {
            margin-bottom: 1.5rem;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #ddd;
        }

        .order-total {
            font-size: 1.25rem;
            font-weight: bold;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px solid #ddd;
        }

        .place-order-btn {
            width: 100%;
            padding: 1rem;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .place-order-btn:hover {
            background: #218838;
        }

        .error-message {
            color: #dc3545;
            background: #f8d7da;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }

        .payment-methods {
            margin: 1.5rem 0;
        }

        .payment-method {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 0.5rem;
            cursor: pointer;
        }

        .payment-method:hover {
            background: #f8f9fa;
        }

        .payment-method input[type="radio"] {
            width: auto;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="checkout-container">
        <div class="checkout-form">
            <h1>Checkout</h1>

            <?php if (isset($error)): ?>
                <div class="error-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" id="checkoutForm">
                <h2>Shipping Information</h2>
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" 
                           value="<?= htmlspecialchars($user_info['full_name']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" 
                           value="<?= htmlspecialchars($user_info['address'] ?? '') ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city" required>
                    </div>
                    <div class="form-group">
                        <label for="state">State/Province</label>
                        <input type="text" id="state" name="state" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="postal_code">Postal Code</label>
                        <input type="text" id="postal_code" name="postal_code" required>
                    </div>
                    <div class="form-group">
                        <label for="country">Country</label>
                        <select id="country" name="country" required>
                            <option value="">Select Country</option>
                            <option value="US">United States</option>
                            <option value="CA">Canada</option>
                            <!-- Add more countries as needed -->
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" 
                           value="<?= htmlspecialchars($user_info['phone'] ?? '') ?>" required>
                </div>

                <h2>Payment Method</h2>
                <div class="payment-methods">
                    <label class="payment-method">
                        <input type="radio" name="payment_method" value="credit_card" required>
                        <i class="fas fa-credit-card"></i>
                        Credit Card
                    </label>
                    <label class="payment-method">
                        <input type="radio" name="payment_method" value="paypal" required>
                        <i class="fab fa-paypal"></i>
                        PayPal
                    </label>
                    <!-- Add more payment methods as needed -->
                </div>

                <div id="credit-card-fields" style="display: none;">
                    <div class="form-group">
                        <label for="card_number">Card Number</label>
                        <input type="text" id="card_number" pattern="[0-9]{16}" 
                               placeholder="1234 5678 9012 3456">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="expiry">Expiry Date</label>
                            <input type="text" id="expiry" placeholder="MM/YY">
                        </div>
                        <div class="form-group">
                            <label for="cvv">CVV</label>
                            <input type="text" id="cvv" pattern="[0-9]{3,4}" placeholder="123">
                        </div>
                    </div>
                </div>

                <button type="submit" class="place-order-btn">
                    Place Order - $<?= number_format($cart_total, 2) ?>
                </button>
            </form>
        </div>

        <div class="order-summary">
            <h2>Order Summary</h2>
            <div class="order-items">
                <?php foreach ($cart_items as $item): ?>
                    <div class="order-item">
                        <div>
                            <span><?= htmlspecialchars($item['name']) ?></span>
                            <small>x<?= $item['quantity'] ?></small>
                        </div>
                        <span>$<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="order-total">
                <div>Total:</div>
                <div>$<?= number_format($cart_total, 2) ?></div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Handle payment method selection
        document.querySelectorAll('input[name="payment_method"]').forEach(input => {
            input.addEventListener('change', function() {
                const creditCardFields = document.getElementById('credit-card-fields');
                creditCardFields.style.display = this.value === 'credit_card' ? 'block' : 'none';
            });
        });

        // Form validation
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
            
            if (!paymentMethod) {
                e.preventDefault();
                alert('Please select a payment method');
                return;
            }

            if (paymentMethod.value === 'credit_card') {
                const cardNumber = document.getElementById('card_number').value;
                const expiry = document.getElementById('expiry').value;
                const cvv = document.getElementById('cvv').value;

                if (!cardNumber || !expiry || !cvv) {
                    e.preventDefault();
                    alert('Please fill in all credit card details');
                    return;
                }
            }
        });

        // Format credit card number
        document.getElementById('card_number').addEventListener('input', function(e) {
            let value = this.value.
