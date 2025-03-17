<?php
require_once 'config/config.php';
require_once 'includes/Database.php';
require_once 'includes/Order.php';

session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    header('Location: index.php');
    exit();
}

$order = new Order();
$order_info = $order->getOrderById($_GET['order_id'], $_SESSION['user_id']);
$order_details = $order->getOrderDetails($_GET['order_id']);

if (!$order_info) {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .confirmation-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .success-message {
            text-align: center;
            margin-bottom: 2rem;
        }

        .success-icon {
            color: #28a745;
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .order-details {
            margin-top: 2rem;
        }

        .order-items {
            margin-top: 1rem;
        }

        .order-item {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 1rem;
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }

        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }

        .shipping-info {
            margin-top: 2rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 4px;
        }

        .action-buttons {
            margin-top: 2rem;
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 4px;
            text-decoration: none;
            text-align: center;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="confirmation-container">
        <div class="success-message">
            <i class="fas fa-check-circle success-icon"></i>
            <h1>Order Confirmed!</h1>
            <p>Thank you for your purchase. Your order has been successfully placed.</p>
            <p>Order #: <?= $order_info['id'] ?></p>
        </div>

        <div class="order-details">
            <h2>Order Summary</h2>
            <div class="order-items">
                <?php foreach ($order_details as $item): ?>
                    <div class="order-item">
                        <?php 
                        $images = json_decode($item['image_urls'], true);
                        $first_image = $images[0] ?? 'assets/images/placeholder.jpg';
                        ?>
                        <img src="<?= htmlspecialchars($first_image) ?>" 
                             alt="<?= htmlspecialchars($item['name']) ?>" 
                             class="item-image">
                        <div class="item-info">
                            <h3><?= htmlspecialchars($item['name']) ?></h3>
                            <p>Quantity: <?= $item['quantity'] ?></p>
                        </div>
                        <div class="item-price">
                            $<?= number_format($item['price'] * $item['quantity'], 2) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="order-total">
                <h3>Total: $<?= number_format($order_info['total_amount'], 2) ?></h3>
            </div>

            <div class="shipping-info">
                <h2>Shipping Information</h2>
                <p><?= htmlspecialchars($order_info['shipping_address']) ?></p>
                <p><?= htmlspecialchars($order_info['shipping_city']) ?>, 
                   <?= htmlspecialchars($order_info['shipping_state']) ?> 
                   <?= htmlspecialchars($order_info['shipping_postal_code']) ?></p>
                <p><?= htmlspecialchars($order_info['shipping_country']) ?></p>
                <p>Phone: <?= htmlspecialchars($order_info['shipping_phone']) ?></p>
            </div>
        </div>

        <div class="action-buttons">
            <a href="orders.php" class="btn btn-primary">View All Orders</a>
            <a href="index.php" class="btn btn-secondary">Continue Shopping</a>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>