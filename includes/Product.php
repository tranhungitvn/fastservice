<?php
class Product {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getFeaturedProducts($limit = 8) {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, s.shop_name, AVG(r.rating) as avg_rating, COUNT(r.id) as review_count
                FROM products p
                LEFT JOIN seller_shops s ON p.seller_id = s.id
                LEFT JOIN reviews r ON p.id = r.product_id
                WHERE p.status = 'active' AND p.is_featured = 1
                GROUP BY p.id
                ORDER BY p.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function getFlashDeals($limit = 6) {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*,
                SELECT p.*, 
                       d.end_date,
                       (p.price - (p.price * d.discount_percentage / 100)) as deal_price
                FROM products p
                JOIN deals d ON p.id = d.product_id
                WHERE p.status = 'active'
                AND d.start_date <= NOW()
                WHERE p.status = 'active' 
                AND d.start_date <= NOW() 
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function getProductById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*,
                SELECT p.*, 
                       s.rating as seller_rating,
                       AVG(r.rating) as product_rating,
                       COUNT(r.id) as review_count
                FROM products p
                LEFT JOIN seller_shops s ON p.seller_id = s.id
                LEFT JOIN reviews r ON p.id = r.product_id
                WHERE p.id = ? AND p.status = 'active'
                GROUP BY p.id
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    public function getProductReviews($product_id, $limit = 10, $offset = 0) {
        try {
            $stmt = $this->db->prepare("
                SELECT r.*, u.username, u.avatar_url
                FROM reviews r
                JOIN users u ON r.user_id = u.id
                WHERE r.product_id = ? AND r.product_id = ?
                LIMIT ? OFFSET ?
            ");
            $stmt->execute([$product_id, $limit, $offset]);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function searchProducts($query, $filters = [], $limit = 20, $offset = 0) {
        try {
            $sql = "
                SELECT p.*, s.shop_name, AVG(r.rating) as avg_rating
                FROM products p
                LEFT JOIN seller_shops s ON p.seller_id = s.id
                LEFT JOIN reviews r ON p.id = r.product_id
                WHERE p.status = 'active'
                AND (p.name LIKE :query OR p.description LIKE :query)
            ";

            // Add filters
            if (!empty($filters['category_id'])) {
                $sql .= " AND p.category_id = :category_id";
            }
            if (!empty($filters['min_price'])) {
                $sql .= " AND p.price >= :min_price";
            }
            if (!empty($filters['max_price'])) {
                $sql .= " AND p.price <= :max_price";
            }
            if (!empty($filters['rating'])) {
                $sql .= " HAVING avg_rating >= :rating";
            }

            $sql .= " GROUP BY p.id ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            
            // Bind parameters
            $queryParam = "%{$query}%";
            $stmt->bindParam(':query', $queryParam);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

            if (!empty($filters['category_id'])) {
                $stmt->bindParam(':category_id', $filters['category_id']);
            }
            if (!empty($filters['min_price'])) {
                $stmt->bindParam(':min_price', $filters['min_price']);
            }
            if (!empty($filters['max_price'])) {
                $stmt->bindParam(':max_price', $filters['max_price']);
            }
            if (!empty($filters['rating'])) {
                $stmt->bindParam(':rating', $filters['rating']);
            }

            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function getFilteredProducts($filters = [], $limit = 20, $offset = 0) {
        try {
            $sql = "
                SELECT p.*, 
                       s.shop_name,
                       AVG(r.rating) as average_rating,
                       COUNT(DISTINCT r.id) as review_count
                FROM products p
                LEFT JOIN seller_shops s ON p.seller_id = s.id
                LEFT JOIN reviews r ON p.id = r.product_id
                WHERE p.status = 'active'
            ";

            $params = [];

            // Category filter
            if (!empty($filters['category_id'])) {
                $sql .= " AND p.category_id = :category_id";
                $params['category_id'] = $filters['category_id'];
                }

            // Search filter
            if (!empty($filters['search'])) {
                $sql .= " AND (p.name LIKE :search OR p.description LIKE :search)";
                $params['search'] = "%{$filters['search']}%";
            }

            // Price range filter
            if (!empty($filters['min_price'])) {
                $sql .= " AND p.price >= :min_price";
                $params['min_price'] = $filters['min_price'];
            }
            if (!empty($filters['max_price'])) {
                $sql .= " AND p.price <= :max_price";
                $params['max_price'] = $filters['max_price'];
            }

            $sql .= " GROUP BY p.id";

            // Sorting
            switch ($filters['sort'] ?? 'newest') {
                case 'price_low':
                    $sql .= " ORDER BY p.price ASC";
                    break;
                case 'price_high':
                    $sql .= " ORDER BY p.price DESC";
                    break;
                case 'popular':
                    $sql .= " ORDER BY review_count DESC";
                    break;
                case 'rating':
                    $sql .= " ORDER BY average_rating DESC";
                    break;
                default:
                    $sql .= " ORDER BY p.created_at DESC";
            }

            $sql .= " LIMIT :limit OFFSET :offset";
            $params['limit'] = $limit;
            $params['offset'] = $offset;

            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function getFilteredProductsCount($filters = []) {
        try {
            $sql = "
                SELECT COUNT(DISTINCT p.id) as total
                FROM products p
                WHERE p.status = 'active'
            ";

            $params = [];

            if (!empty($filters['category_id'])) {
                $sql .= " AND p.category_id = :category_id";
                $params['category_id'] = $filters['category_id'];
            }

            if (!empty($filters['search'])) {
                $sql .= " AND (p.name LIKE :search OR p.description LIKE :search)";
                $params['search'] = "%{$filters['search']}%";
            }

            if (!empty($filters['min_price'])) {
                $sql .= " AND p.price >= :min_price";
                $params['min_price'] = $filters['min_price'];
            }
            if (!empty($filters['max_price'])) {
                $sql .= " AND p.price <= :max_price";
                $params['max_price'] = $filters['max_price'];
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn();
        } catch(PDOException $e) {
            error_log($e->getMessage());
            return 0;
        }
    }

    public function addProduct($data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO products (
                    seller_id, category_id, name, description, price,
                    stock_quantity, image_urls, status
                ) VALUES (
                    :seller_id, :category_id, :name, :description, :price,
                    :stock_quantity, :image_urls, :status
                )
            ");

            return $stmt->execute([
                'seller_id' => $data['seller_id'],
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'description' => $data['description'],
                'price' => $data['price'],
                'stock_quantity' => $data['stock_quantity'],
                'image_urls' => json_encode($data['image_urls']),
                'status' => $data['status'] ?? 'active'
            ]);
        } catch(PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function updateProduct($id, $data) {
        try {
            $sql = "UPDATE products SET ";
            $params = [];

            foreach ($data as $key => $value) {
                if ($key === 'image_urls') {
                    $value = json_encode($value);
}
                $sql .= "$key = :$key, ";
                $params[$key] = $value;
            }

            $sql = rtrim($sql, ", ");
            $sql .= " WHERE id = :id";
            $params['id'] = $id;

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch(PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function deleteProduct($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM products WHERE id = ?");
            return $stmt->execute([$id]);
        } catch(PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function updateStock($id, $quantity) {
        try {
            $stmt = $this->db->prepare("
                UPDATE products
                SET stock_quantity = stock_quantity + :quantity,
                    status = CASE
                        WHEN stock_quantity + :quantity <= 0 THEN 'out_of_stock'
                        ELSE status
                    END
                WHERE id = :id
            ");
            return $stmt->execute([
                'id' => $id,
                'quantity' => $quantity
            ]);
        } catch(PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function addReview($data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO reviews (
                    user_id, product_id, rating, comment, images
                ) VALUES (
                    :user_id, :product_id, :rating, :comment, :images
                )
            ");

            return $stmt->execute([
                'user_id' => $data['user_id'],
                'product_id' => $data['product_id'],
                'rating' => $data['rating'],
                'comment' => $data['comment'],
                'images' => json_encode($data['images'] ?? [])
            ]);
        } catch(PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}