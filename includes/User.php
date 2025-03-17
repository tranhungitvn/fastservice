<?php
class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function register($data) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO users (username, email, password_hash, full_name, phone)
                VALUES (:username, :email, :password, :full_name, :phone)
            ");

            return $stmt->execute([
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'full_name' => $data['full_name'],
                'phone' => $data['phone']
            ]);
        } catch(PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function login($email, $password) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM users WHERE email = :email
            ");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['username'] = $user['username'];
                return true;
            }
            return false;
        } catch(PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function logout() {
        session_destroy();
        return true;
    }

    public function getCurrentUser() {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        try {
            $stmt = $this->db->prepare("
                SELECT id, username, email, role, full_name, phone, address 
                FROM users WHERE id = :id
            ");
            $stmt->execute(['id' => $_SESSION['user_id']]);
            return $stmt->fetch();
        } catch(PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }
}