<?php
include_once __DIR__ . '/../config/config.php';

class User {
    private $pdo;

    public function __construct() {
        global $pdo; // Utiliser la variable globale $pdo définie dans config/config.php
        $this->pdo = $pdo;
    }

    public function register($username, $password, $role) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        return $stmt->execute([$username, $hashed_password, $role]);
    }

    public function login($username, $password) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            return true;
        }
        return false;
    }
}
?>
