<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/Auth.php';

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = sanitize($_POST['username']);
            $email = sanitize($_POST['email']);
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];

            if ($password !== $confirmPassword) {
                $error = "Passwords do not match";
                require_once __DIR__ . '/../views/auth/register.php';
                return;
            }

            if ($this->userModel->findByUsername($username)) {
                $error = "Username already exists";
                require_once __DIR__ . '/../views/auth/register.php';
                return;
            }

            if ($this->userModel->findByEmail($email)) {
                $error = "Email already exists";
                require_once __DIR__ . '/../views/auth/register.php';
                return;
            }

            if ($this->userModel->create($username, $email, $password)) {
                redirect('/login');
            } else {
                $error = "Registration failed";
                require_once __DIR__ . '/../views/auth/register.php';
            }
        } else {
            require_once __DIR__ . '/../views/auth/register.php';
        }
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = sanitize($_POST['username']);
            $password = $_POST['password'];

            $user = $this->userModel->findByUsername($username);

            if ($user && $this->userModel->verifyPassword($password, $user['password'])) {
                Auth::login($user['id'], $user['username']);
                redirect('/');
            } else {
                $error = "Invalid credentials";
                require_once __DIR__ . '/../views/auth/login.php';
            }
        } else {
            require_once __DIR__ . '/../views/auth/login.php';
        }
    }

    public function logout() {
        Auth::logout();
        redirect('/');
    }
}
