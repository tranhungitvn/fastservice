<header class="main-header">
    <div class="container">
        <nav class="main-nav">
            <div class="logo">
                <a href="index.php"><?= SITE_NAME ?></a>
            </div>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <a href="products.php">Products</a>
                <a href="categories.php">Categories</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="cart.php">Cart</a>
                    <a href="account.php">My Account</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </nav>
    </div>
</header>