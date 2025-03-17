<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Existing styles */
    </style>
</head>
<body>
    <div class="product-container">
        <div class="product-grid">
            <div class="product-images">
                <img src="<?= htmlspecialchars($image) ?>" alt="Main image" class="main-image" onclick="changeMainImage(this.src)">
                <div class="thumbnail-grid">
                    <?php foreach ($product['images'] as $image): ?>
                        <img src="<?= htmlspecialchars($image) ?>" alt="Thumbnail image" class="thumbnail" onclick="changeMainImage(this.src)">
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="product-info">
                <h1><?= htmlspecialchars($product['name']) ?></h1>
                <p class="product-price"><?= htmlspecialchars($product['price']) ?></p>
                <div class="product-rating">
                    <span class="stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fa-star<?= $i <= $product['rating'] ? '-o' : '' ?>"></i>
                        <?php endfor; ?>
                    </span>
                </div>
                <div class="stock-info">
                    <?php if ($product['stock_quantity'] > 0): ?>
                        <span class="in-stock">In Stock</span>
                    <?php else: ?>
                        <span class="out-of-stock">Out of Stock</span>
                    <?php endif; ?>
                </div>
                <div class="quantity-selector">
                    <button onclick="updateQuantity(-1)" disabled>−</button>
                    <input type="number" id="quantity" value="1" min="1" max="<?= $product['stock_quantity'] ?>">
                    <button onclick="updateQuantity(1)">+</button>
                </div>
                <button class="add-to-cart-btn" onclick="addToCart(<?= $product['id'] ?>)">Add to Cart</button>
                <div class="product-description">
                    <?= htmlspecialchars($product['description']) ?>
                </div>
                <div class="seller-info">
                    <h2>Seller Information</h2>
                    <p><?= htmlspecialchars($product['seller_name']) ?></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
