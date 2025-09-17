<?php
session_start();

class Auth {
    public static function login($userId, $username) {
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
    }

    public static function logout() {
        session_destroy();
    }

    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public static function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    public static function getUsername() {
        return $_SESSION['username'] ?? null;
    }
}
