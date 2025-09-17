<?php
session_start();
require_once 'utils/Database.php';
require_once 'models/User.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Пароли не совпадают";
    } else {
        $userModel = new User();

        if ($userModel->findByUsername($username)) {
            $error = "Имя пользователя уже занято";
        } elseif ($userModel->findByEmail($email)) {
            $error = "Email уже используется";
        } elseif ($userModel->create($username, $email, $password)) {
            $success = "Регистрация успешна! Теперь войдите.";
            header('Refresh: 2; URL=/login.php');
        } else {
            $error = "Ошибка регистрации";
        }
    }
}

// HTML содержимое для регистрации
$content = '
<h2>Регистрация</h2>
<form method="post">
    <div>
        <input type="text" name="username" placeholder="Имя пользователя" value="' . (isset($username) ? htmlspecialchars($username) : '') . '" required>
    </div>
    <div>
        <input type="email" name="email" placeholder="Email" value="' . (isset($email) ? htmlspecialchars($email) : '') . '" required>
    </div>
    <div>
        <input type="password" name="password" placeholder="Пароль" required>
    </div>
    <div>
        <input type="password" name="confirm_password" placeholder="Подтвердите пароль" required>
    </div>
    <button type="submit">Зарегистрироваться</button>
</form>
<p>Уже есть аккаунт? <a href="/login.php">Войдите здесь</a></p>
';

// Включаем layout
include 'views/layout.php';
