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
$limit = 12; // Products per page
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

