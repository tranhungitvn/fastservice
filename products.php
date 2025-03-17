<?php
require_once 'config/config.php';
require_once 'includes/Database.php';
require_once 'includes/Product.php';
require_once 'includes/Category.php';
require_once 'includes/header.php';

session_start();

$product = new Product();
$category = new Category();

// Get all categories for filter
$categories = $category->getAllCategories();

// Pagination settings
$limit = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filter and sort parameters
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : null;
$maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : null;

// Get filtered products
$products = $product->getFilteredProducts([
    'category_id' => $categoryId,
    'search' => $search,
    'sort' => $sort,
    'min_price' => $minPrice,
    'max_price' => $maxPrice,
    'limit' => $limit,
    'offset' => $offset
]);

// Get total products count for pagination
$totalProducts = $product->getFilteredProductsCount([
    'category_id' => $categoryId,
    'search' => $search,
    'min_price' => $minPrice,
    'max_price' => $maxPrice
]);

$totalPages = ceil($totalProducts / $limit);
?>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Filters Sidebar -->
        <div class="col-lg-3">
            <div class="filters-wrapper sticky-top" style="top: 20px;">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-filter me-2"></i>Filters
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="filterForm" method="GET" action="products.php">
                            <!-- Search -->
                            <div class="mb-4">
                                <label for="search" class="form-label fw-bold">Search Products</label>
                                <div class="input-group">
                                    <input type="text" 
                                           class="form-control" 
                                           id="search" 
                                           name="search" 
                                           placeholder="Search products..."
                                           value="<?= htmlspecialchars($search) ?>">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <!-- Categories -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Category</label>
                                <select class="form-select" name="category">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" 
                                                <?= ($categoryId == $cat['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <!-- Price Range -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Price Range</label>
                                <div class="row g-2">
                                    <div class="col">
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" 
                                                   class="form-control" 
                                                   name="min_price" 
                                                   placeholder="Min"
                                                   value="<?= $minPrice ?>">
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" 
                                                   class="form-control" 
                                                   name="max_price" 
                                                   placeholder="Max"
                                                   value="<?= $maxPrice ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sort -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Sort By</label>
                                <select class="form-select" name="sort">
                                    <option value="newest" <?= ($sort == 'newest') ? 'selected' : '' ?>>
                                        Newest Arrivals
                                    </option>
                                    <option value="price_low" <?= ($sort == 'price_low') ? 'selected' : '' ?>>
                                        Price: Low to High
                                    </option>
                                    <option value="price_high" <?= ($sort == 'price_high') ? 'selected' : '' ?>>
                                        Price: High to Low
                                    </option>
                                    <option value="popular" <?= ($sort == 'popular') ? 'selected' : '' ?>>
                                        Most Popular
                                    </option>
                                    <option value="rating" <?= ($sort == 'rating') ? 'selected' : '' ?>>
                                        Highest Rated
                                    </option>
                                </select>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Apply Filters
                                </button>
                                <a href="products.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-undo me-2"></i>Clear Filters
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="col-lg-9">
            <!-- Results Summary -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="results-info">
                    <h4 class="mb-1">Products</h4>
                    <p class="text-muted mb-0">
                        Showing <?= $offset + 1 ?>-<?= min($offset + $limit, $totalProducts) ?> 
                        of <?= $totalProducts ?> products
                    </p>
                </div>
            </div>

            <?php if (empty($products)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>No products found matching your criteria.
                </div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <?php foreach ($products as $prod): ?>
                        <div class="col">
                            <div class="card h-100 product-card shadow-sm">
                                <!-- Product Image -->
                                <div class="product-image-wrapper position-relative">
                                    <?php if ($prod['discount_price'] > 0): ?>
                                        <div class="discount-badge">
                                            <?= round((($prod['price'] - $prod['discount_price']) / $prod['price']) * 100) ?>% OFF
                                        </div>
                                    <?php endif; ?>
                                    <img src="<?= htmlspecialchars($prod['image_urls']) ?>" 
                                         class="card-img-top" 
                                         alt="<?= htmlspecialchars($prod['name']) ?>">
                                </div>
                                
                                <div class="card-body">
                                    <h5 class="card-title product-title">
                                        <a href="product.php?id=<?= $prod['id'] ?>" 
                                           class="text-decoration-none text-dark">
                                            <?= htmlspecialchars($prod['name']) ?>
                                        </a>
                                    </h5>

                                    <!-- Rating -->
                                    <div class="mb-2">
                                        <div class="rating">
                                            <?php
                                            $rating = round($prod['average_rating']);
                                            for ($i = 1; $i <= 5; $i++):
                                                if ($i <= $rating): ?>
                                                    <i class="fas fa-star text-warning"></i>
                                                <?php else: ?>
                                                    <i class="far fa-star text-warning"></i>
                                                <?php endif;
                                            endfor; ?>
                                            <span class="ms-2 text-muted">
                                                (<?= $prod['review_count'] ?> reviews)
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Price -->
                                    <div class="mb-2 product-price">
                                        <?php if ($prod['discount_price'] > 0): ?>
                                            <span class="text-danger h5">
                                                $<?= number_format($prod['discount_price'], 2) ?>
                                            </span>
                                            <span class="text-muted text-decoration-line-through ms-2">
                                                $<?= number_format($prod['price'], 2) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="h5">
                                                $<?= number_format($prod['price'], 2) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Stock Status -->
                                    <p class="card-text">
                                        <?php if ($prod['stock_quantity'] > 0): ?>
                                            <span class="text-success">
                                                <i class="fas fa-check-circle me-1"></i>In Stock
                                            </span>
                                        <?php else: ?>
                                            <span class="text-danger">
                                                <i class="fas fa-times-circle me-1"></i>Out of Stock
                                            </span>
                                        <?php endif; ?>
                                    </p>

                                    <!-- Add to Cart Button -->
                                    <?php if ($prod['stock_quantity'] > 0): ?>
                                        <button class="btn btn-primary w-100 add-to-cart-btn" 
                                                data-product-id="<?= $prod['id'] ?>">
                                            <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Product pagination" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="products.php?page=<?= $page - 1 ?>">
                                        <i class="fas fa-angle-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item">
                                    <a class="page-link" href="products.php?page=<?= $i ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="products.php?page=<?= $page + 1 ?>">
                                        <i class="fas fa-angle-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="row">
            <!-- Filters Sidebar -->
            <div class="col-lg-3">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Filters</h5>
                    </div>
                    <div class="card-body">
                        <form id="filterForm" method="GET" action="products.php">
                            <!-- Search -->
                            <div class="mb-3">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?= htmlspecialchars($search) ?>">
                            </div>

                            <!-- Categories -->
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select class="form-select" name="category">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" 
                                                <?= ($categoryId == $cat['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Price Range -->
                            <div class="mb-3">
                                <label class="form-label">Price Range</label>
                                <div class="d-flex gap-2">
                                    <input type="number" class="form-control" name="min_price" 
                                           placeholder="Min" value="<?= $minPrice ?>">
                                    <input type="number" class="form-control" name="max_price" 
                                           placeholder="Max" value="<?= $maxPrice ?>">
                                </div>
                            </div>

                            <!-- Sort -->
                            <div class="mb-3">
                                <label class="form-label">Sort By</label>
                                <select class="form-select" name="sort">
                                    <option value="newest" <?= ($sort == 'newest') ? 'selected' : '' ?>>Newest</option>
                                    <option value="price_low" <?= ($sort == 'price_low') ? 'selected' : '' ?>>Price: Low to High</option>
                                    <option value="price_high" <?= ($sort == 'price_high') ? 'selected' : '' ?>>Price: High to Low</option>
                                    <option value="popular" <?= ($sort == 'popular') ? 'selected' : '' ?>>Most Popular</option>
                                    <option value="rating" <?= ($sort == 'rating') ? 'selected' : '' ?>>Highest Rated</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                            <a href="products.php" class="btn btn-outline-secondary w-100 mt-2">Clear Filters</a>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="col-lg-9">
                <!-- Results Summary -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <p class="mb-0">
                        Showing <?= $offset + 1 ?>-<?= min($offset + $limit, $totalProducts) ?> 
                        of <?= $totalProducts ?> products
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>