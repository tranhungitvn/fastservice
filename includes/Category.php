<?php
class Category {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllCategories() {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, 
                       COUNT(p.id) as product_count,
                       parent.name as parent_name
                FROM categories c
                LEFT JOIN categories parent ON c.parent_id = parent.id
                LEFT JOIN products p ON c.id = p.category_id
                WHERE c.status = 'active'
                GROUP BY c.id
                ORDER BY c.parent_id, c.name
            ");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function getPopularCategories($limit = 6) {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, 
                       COUNT(p.id) as product_count,
                       SUM(od.quantity) as total_sales
                FROM categories c
                LEFT JOIN products p ON c.id = p.category_id
                LEFT JOIN order_details od ON p.id = od.product_id
                WHERE c.status = 'active'
                GROUP BY c.id
                ORDER BY total_sales DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function getCategoryById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, 
                       parent.name as parent_name,
                       COUNT(p.id) as product_count
                FROM categories c
                LEFT JOIN categories parent ON c.parent_id = parent.id
                LEFT JOIN products p ON c.id = p.category_id
                WHERE c.id = ? AND c.status = 'active'
                GROUP BY c.id
            ");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    public function getSubcategories($parent_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, 
                       COUNT(p.id) as product_count
                FROM categories c
                LEFT JOIN products p ON c.id = p.category_id
                WHERE c.parent_id = ? AND c.status = 'active'
                GROUP BY c.id
                ORDER BY c.name
            ");
            $stmt->execute([$parent_id]);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function addCategory($data) {
        try {
            // Generate slug from name
            $slug = $this->generateSlug($data['name']);

            $stmt = $this->db->prepare("
                INSERT INTO categories (
                    name, slug, description, parent_id, 
                    image_url, status, meta_title, meta_description
                ) VALUES (
                    :name, :slug, :description, :parent_id,
                    :image_url, :status, :meta_title, :meta_description
                )
            ");

            return $stmt->execute([
                'name' => $data['name'],
                'slug' => $slug,
                'description' => $data['description'] ?? null,
                'parent_id' => $data['parent_id'] ?? null,
                'image_url' => $data['image_url'] ?? null,
                'status' => $data['status'] ?? 'active',
                'meta_title' => $data['meta_title'] ?? $data['name'],
                'meta_description' => $data['meta_description'] ?? null
            ]);
        } catch(PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function updateCategory($id, $data) {
        try {
            $sql = "UPDATE categories SET ";
            $params = [];
            
            foreach ($data as $key => $value) {
                if ($key === 'name') {
                    // Update slug if name is changed
                    $data['slug'] = $this->generateSlug($value);
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

    public function deleteCategory($id) {
        try {
            // Check if category has subcategories
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM categories WHERE parent_id = ?");
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Cannot delete category with subcategories");
            }

            // Check if category has products
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
            $stmt->execute([$id]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("Cannot delete category with associated products");
            }

            $stmt = $this->db->prepare("DELETE FROM categories WHERE id = ?");
            return $stmt->execute([$id]);
        } catch(Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function getCategoryBreadcrumb($category_id) {
        try {
            $breadcrumb = [];
            $current_id = $category_id;

            while ($current_id) {
                $stmt = $this->db->prepare("
                    SELECT id, name, parent_id, slug
                    FROM categories
                    WHERE id = ?
                ");
                $stmt->execute([$current_id]);
                $category = $stmt->fetch();

                if ($category) {
                    array_unshift($breadcrumb, $category);
                    $current_id = $category['parent_id'];
                } else {
                    break;
                }
            }

            return $breadcrumb;
        } catch(PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function getCategoryTree() {
        try {
            // Get all categories
            $categories = $this->getAllCategories();
            
            // Build tree structure
            $tree = [];
            foreach ($categories as $category) {
                if (!$category['parent_id']) {
                    $tree[$category['id']] = $category;
                    $tree[$category['id']]['children'] = [];
                }
            }
            
            foreach ($categories as $category) {
                if ($category['parent_id'] && isset($tree[$category['parent_id']])) {
                    $tree[$category['parent_id']]['children'][] = $category;
                }
            }

            return $tree;
        } catch(PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    private function generateSlug($name) {
        // Convert to lowercase and replace spaces with hyphens
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        
        // Check if slug already exists
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM categories WHERE slug = ?");
        $stmt->execute([$slug]);
        $count = $stmt->fetchColumn();

        // If slug exists, append number
        if ($count > 0) {
            $original_slug = $slug;
            $i = 1;
            do {
                $slug = $original_slug . '-' . $i++;
                $stmt->execute([$slug]);
                $count = $stmt->fetchColumn();
            } while ($count > 0);
        }

        return $slug;
    }

    public function searchCategories($query) {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, 
                       COUNT(p.id) as product_count
                FROM categories c
                LEFT JOIN products p ON c.id = p.category_id
                WHERE c.status = 'active' 
                AND (c.name LIKE :query OR c.description LIKE :query)
                GROUP BY c.id
                ORDER BY c.name
            ");
            
            $query = "%{$query}%";
            $stmt->execute(['query' => $query]);
            return $stmt->fetchAll();
        } catch(PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function getCategoryProducts($category_id, $filters = [], $page = 1, $per_page = 20) {
        try {
            $offset = ($page - 1) * $per_page;
            
            $sql = "
                SELECT p.*, s.shop_name, AVG(r.rating) as avg_rating
                FROM products p
                LEFT JOIN seller_shops s ON p.seller_id = s.id
                LEFT JOIN reviews r ON p.id = r.product_id
                WHERE p.category_id = :category_id 
                AND p.status = 'active'
            ";

            // Add filters
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
            $stmt->bindParam(':category_id', $category_id);
            $stmt->bindParam(':limit', $per_page, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

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
}