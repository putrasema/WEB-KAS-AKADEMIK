<?php

class Auth
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function register($username, $password, $fullName, $role = 'student')
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (username, password, full_name, role) VALUES (?, ?, ?, ?)");
        try {
            return $stmt->execute([$username, $hash, $fullName, $role]);
        } catch (PDOException $e) {
            return false; // Likely duplicate username
        }
    }

    public function login($username, $password)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            return true;
        }
        return false;
    }

    public function logout()
    {
        session_unset();
        session_destroy();
    }

    public function getCurrentUser()
    {
        if (isset($_SESSION['user_id'])) {
            return [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'role' => $_SESSION['role'],
                'full_name' => $_SESSION['full_name']
            ];
        }
        return null;
    }

    public function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    public function requireLogin()
    {
        if (!$this->isLoggedIn()) {
            header("Location: login.php");
            exit();
        }
    }
}
