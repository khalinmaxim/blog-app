<?php
session_start();
require_once 'utils/Database.php';
require_once 'models/User.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $userModel = new User();
    $user = $userModel->findByUsername($username);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: /');
        exit;
    } else {
        $error = "Неверное имя пользователя или пароль";
    }
}

// HTML содержимое для входа
$content = '
<h2>Вход</h2>
<form method="post">
    <div>
        <input type="text" name="username" placeholder="Имя пользователя" value="' . (isset($username) ? htmlspecialchars($username) : '') . '" required>
    </div>
    <div>
        <input type="password" name="password" placeholder="Пароль" required>
    </div>
    <button type="submit">Войти</button>
</form>
<p>Нет аккаунта? <a href="/register.php">Зарегистрируйтесь здесь</a></p>
';

// Включаем layout
include 'views/layout.php';
