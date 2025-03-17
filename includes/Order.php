<?php
class Order {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function createOrder($user_id, $cart_items, $shipping_info) {
        try {
            $this->db->beginTransaction();

            // Calculate total amount
            $total_amount = 0;
            foreach ($cart_items as $item) {
                $total_amount += $item['price'] * $item['quantity'];
            }

            // Create order
            $stmt = $this->db->prepare("
                INSERT INTO orders (
                    user_id, total_amount, shipping_address, 
                    shipping_city, shipping_state, shipping_postal_code,
                    shipping_country, shipping_phone, status, payment_status
                ) VALUES (
                    :user_id, :total_amount, :address,
                    :city, :state, :postal_code,
                    :country, :phone, 'pending', 'pending'
                )
            ");

            $stmt->execute([
                'user_id' => $user_id,
                'total_amount' => $total_amount,
                'address' => $shipping_info['address'],
                'city' => $shipping_info['city'],
                'state' => $shipping_info['state'],
                'postal_code' => $shipping_info['postal_code'],
                'country' => $shipping_info['country'],
                'phone' => $shipping_info['phone']
            ]);

            $order_id = $this->db->lastInsertId();

            // Create order details
            $stmt = $this->db->prepare("
                INSERT INTO order_details (
                    order_id, product_id, quantity, price
                ) VALUES (
                    :order_id, :product_id, :quantity, :price
                )
            ");

            foreach ($cart_items as $item) {
                $stmt->execute([
                    'order_id' => $order_id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ]);

                // Update product stock
                $this->updateProductStock($item['product_id'], $item['quantity']);
            }

            $this->db->commit();
            return ['success' => true, 'order_id' => $order_id];
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log($e->getMessage());
            return ['success' => false, 'message' => 'Error processing order'];
        }
    }

    private function updateProductStock($product_id, $quantity) {
        $stmt = $this->db->prepare("
            UPDATE products 
            SET stock_quantity = stock_quantity - :quantity
            WHERE id = :product_id
        ");
        return $stmt->execute([
            'product_id' => $product_id,
            'quantity' => $quantity
        ]);
    }

    public function getOrderById($order_id, $user_id = null) {
        try {
            $sql = "
                SELECT o.*, u.username, u.email
                FROM orders o
                JOIN users u ON o.user_id = u.id
                WHERE o.id = ?
            ";
            
            if ($user_id) {
                $sql .= " AND o.user_id = ?";
            }

            $stmt = $this->db->prepare($sql);
            
            if ($user_id) {
                $stmt->execute([$order_id, $user_id]);
            } else {
                $stmt->execute([$order_id]);
            }

            return $stmt->fetch();
        } catch (Exception $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    public function getOrderDetails($order_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT od.*, p.name, p.image_urls
                FROM order_details od
                JOIN products p ON od.product_id = p.id
                WHERE od.order_id = ?
            ");
            $stmt->execute([$order_id]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function getUserOrders($user_id) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM orders 
                WHERE user_id = ?
                ORDER BY created_at DESC
            ");
            $stmt->execute([$user_id]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    public function updateOrderStatus($order_id, $status) {
        try {
            $stmt = $this->db->prepare("
                UPDATE orders 
                SET status = ?
                WHERE id = ?
            ");
            return $stmt->execute([$status, $order_id]);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}