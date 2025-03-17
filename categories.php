    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .categories-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .category-search {
            margin-bottom: 2rem;
        }

        .search-form {
            display: flex;
            gap: 1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .search-input {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .search-btn {
            padding: 0.75rem 1.5rem;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 2rem;
        }

        .category-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .category-card:hover {
            transform: translateY(-5px);
        }

        .category-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }

        .category-info {
            padding: 1rem;
        }

        .category-name {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .category-description {
            color: #666;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .category-stats {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            color: #666;
        }

        .subcategories {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
        }

        .subcategory-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .subcategory-item {
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }

        .subcategory-item:last-child {
            border-bottom: none;
        }

        .category-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .category-link:hover {
            color: #007bff;
        }

        .breadcrumb {
            margin-bottom: 2rem;
            padding: 0.5rem 0;
        }

        .breadcrumb-list {
            list-style: none;
            padding: 0;
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .breadcrumb-item:not(:last-child)::after {
            content: '>';
            margin-left: 0.5rem;
            color: #666;
        }

        .no-results {
            text-align: center;
            padding: 3rem;
            color: #666;
        }

        /* Tree view styles */
        .category-tree {
            margin-bottom: 2rem;
        }

        .tree-item {
            margin: 0.5rem 0;
        }

        .tree-parent {
            font-weight: bold;
        }

        .tree-children {
            margin-left: 1.5rem;
            padding-left: 1rem;
            border-left: 1px solid #ddd;
        }

        @media (max-width: 768px) {
            .categories-grid {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="categories-container">
        <div class="breadcrumb">
            <ul class="breadcrumb-list">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item">Categories</li>
            </ul>
        </div>

        <div class="category-search">
            <form class="search-form" method="GET">
                <input type="text" name="q" class="search-input" 
                       placeholder="Search categories..."
                       value="<?= htmlspecialchars($search_query ?? '') ?>">
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i> Search
                </button>
            </form>
        </div>

        <?php if ($search_query): ?>
            <h2>Search Results for "<?= htmlspecialchars($search_query) ?>"</h2>
            <?php if (empty($categories)): ?>
                <div class="no-results">
                    <i class="fas fa-search" style="font-size: 3rem; color: #ddd;"></i>
                    <p>No categories found matching your search.</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="categories-grid">
            <?php foreach ($categories as $category): ?>
                <div class="category-card">
                    <a href="category.php?id=<?= $category['id'] ?>" class="category-link">
                        <img src="<?= htmlspecialchars($category['image_url'] ?? 'assets/images/category-placeholder.jpg') ?>" 
                             alt="<?= htmlspecialchars($category['name']) ?>" 
                             class="category-image">
                        <div class="category-info">
                            <h3 class="category-name"><?= htmlspecialchars($category['name']) ?></h3>
                            <?php if (!empty($category['description'])): ?>
                                <p class="category-description">
                                    <?= htmlspecialchars(substr($category['description'], 0, 100)) ?>...
                                </p>
                            <?php endif; ?>
                            <div class="category-stats">
                                <span><?= $category['product_count'] ?> Products</span>
                                <?php if (!empty($category['children'])): ?>
                                    <span><?= count($category['children']) ?> Subcategories</span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($category['children'])): ?>
                                <div class="subcategories">
                                    <ul class="subcategory-list">
                                        <?php foreach (array_slice($category['children'], 0, 3) as $child): ?>
                                            <li class="subcategory-item">
                                                <a href="category.php?id=<?= $child['id'] ?>" class="category-link">
                                                    <?= htmlspecialchars($child['name']) ?>
                                                    <span>(<?= $child['product_count'] ?>)</span>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                        <?php if (count($category['children']) > 3): ?>
                                            <li class="subcategory-item">
                                                <a href="category.php?id=<?= $category['id'] ?>" class="category-link">
                                                    View all <?= count($category['children']) ?> subcategories...
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Add smooth scrolling to category links
        document.querySelectorAll('.category-link').forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href.startsWith('#')) {
                    e.preventDefault();
                    document.querySelector(href).scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Add search input enhancement
        const searchInput = document.querySelector('.search-input');
        let searchTimeout;

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.length >= 2) {
                    this.form.submit();
                }
            }, 500);
        });
    </script>
</body>
</html>