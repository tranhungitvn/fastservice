<?php
require_once 'config/config.php';
require_once 'includes/Database.php';
require_once 'includes/User.php';
require_once 'includes/Product.php';
require_once 'includes/Category.php';

session_start();

$db = Database::getInstance();
$product = new Product();
$category = new Category();

// Get featured products
$featured_products = $product->getFeaturedProducts();

// Get popular categories
$popular_categories = $category->getPopularCategories();

// Get flash deals
$flash_deals = $product->getFlashDeals();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?> - Online Shopping Platform</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Hero Section */
        .hero {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('assets/images/hero-bg.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            text-align: center;
        }

        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        /* Categories Section */
        .categories {
            padding: 3rem 0;
        }

        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            padding: 2rem 0;
        }

        .category-card {
            text-align: center;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .category-card:hover {
            transform: translateY(-5px);
        }

        /* Featured Products */
        .featured-products {
            padding: 3rem 0;
            background: #f9f9f9;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            padding: 2rem 0;
        }

        .product-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .product-info {
            padding: 1rem;
        }

        .product-price {
            font-size: 1.25rem;
            color: #e44d26;
            font-weight: bold;
        }

        /* Flash Deals */
        .flash-deals {
            padding: 3rem 0;
        }

        .deal-card {
            position: relative;
            overflow: hidden;
        }

        .deal-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #e44d26;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
        }

        /* Newsletter */
        .newsletter {
            background: #2c3e50;
            color: white;
            padding: 3rem 0;
            text-align: center;
        }

        .newsletter-form {
            max-width: 500px;
            margin: 2rem auto;
        }

        .newsletter-form input {
            width: 70%;
            padding: 0.75rem;
            border: none;
            border-radius: 4px 0 0 4px;
        }

        .newsletter-form button {
            width: 30%;
            padding: 0.75rem;
            border: none;
            background: #e44d26;
            color: white;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Welcome to <?= SITE_NAME ?></h1>
            <p>Your One-Stop Shop for Everything You Need</p>
            <a href="products.php" class="btn-primary">Shop Now</a>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="categories">
        <div class="container">
            <h2>Popular Categories</h2>
            <div class="category-grid">
                <?php foreach ($popular_categories as $category): ?>
                    <div class="category-card">
                        <img src="<?= htmlspecialchars($category['image_url']) ?>" alt="<?= htmlspecialchars($category['name']) ?>">
                        <h3><?= htmlspecialchars($category['name']) ?></h3>
                        <p><?= htmlspecialchars($category['description']) ?></p>
                        <a href="category.php?id=<?= $category['id'] ?>" class="btn-secondary">View Products</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Flash Deals -->
    <section class="flash-deals">
        <div class="container">
            <h2>Flash Deals</h2>
            <div class="product-grid">
                <?php foreach ($flash_deals as $deal): ?>
                    <div class="product-card deal-card">
                        <span class="deal-badge">-<?= $deal['discount'] ?>%</span>
                        <img src="<?= htmlspecialchars($deal['image_url']) ?>" alt="<?= htmlspecialchars($deal['name']) ?>" class="product-image">
                        <div class="product-info">
                            <h3><?= htmlspecialchars($deal['name']) ?></h3>
                            <p class="product-price">
                                <span class="original-price">$<?= number_format($deal['original_price'], 2) ?></span>
                                <span class="deal-price">$<?= number_format($deal['deal_price'], 2) ?></span>
                            </p>
                            <div class="deal-timer" data-ends="<?= $deal['ends_at'] ?>">
                                Time Left: <span class="countdown"></span>
                            </div>
                            <button class="btn-primary add-to-cart" data-product-id="<?= $deal['id'] ?>">Add to Cart</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="featured-products">
        <div class="container">
            <h2>Featured Products</h2>
            <div class="product-grid">
                <?php foreach ($featured_products as $product): ?>
                    <div class="product-card">
                        <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-image">
                        <div class="product-info">
                            <h3><?= htmlspecialchars($product['name']) ?></h3>
                            <p><?= htmlspecialchars($product['short_description']) ?></p>
                            <p class="product-price">$<?= number_format($product['price'], 2) ?></p>
                            <button class="btn-primary add-to-cart" data-product-id="<?= $product['id'] ?>">Add to Cart</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="newsletter">
        <div class="container">
            <h2>Subscribe to Our Newsletter</h2>
            <p>Get the latest updates about new products and special offers!</p>
            <form class="newsletter-form" id="newsletterForm">
                <input type="email" placeholder="Enter your email" required>
                <button type="submit">Subscribe</button>
            </form>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Add to Cart functionality
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.productId;
                addToCart(productId);
            });
        });

        function addToCart(productId) {
            fetch('ajax/add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Product added to cart!');
                    updateCartCount(data.cart_count);
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error adding product to cart');
            });
        }

        // Deal countdown timer
        document.querySelectorAll('.deal-timer').forEach(timer => {
            const endTime = new Date(timer.dataset.ends).getTime();
            
            const countdown = timer.querySelector('.countdown');
            
            const interval = setInterval(() => {
                const now = new Date().getTime();
                const distance = endTime - now;

                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                countdown.textContent = `${hours}h ${minutes}m ${seconds}s`;

                if (distance < 0) {
                    clearInterval(interval);
                    countdown.textContent = "EXPIRED";
                }
            }, 1000);
        });

        // Newsletter subscription
        document.getElementById('newsletterForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[type="email"]').value;
            
            fetch('ajax/subscribe_newsletter.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email: email })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Thank you for subscribing!');
                    this.reset();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error subscribing to newsletter');
            });
        });
    </script>
</body>
</html>
