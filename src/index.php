<?php

// Простая маршрутизация
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Убираем повторяющиеся слеши
$path = preg_replace('#/+#', '/', $path);

// Убираем завершающий слеш
$path = rtrim($path, '/');

if ($path === '') {
    $path = '/';
}

// Маршрутизация
switch ($path) {
    case '/':
        include 'views/home.php';
        break;
    case '/register.php':
        include 'register.php';
        break;
    case '/login.php':
        include 'login.php';
        break;
    case '/logout.php':
        include 'logout.php';
        break;
    case '/init_database.php':
        include 'init_database.php';
        break;
    case '/test_db.php':
        include 'test_db.php';
        break;
    case '/posts/create.php':
        include 'posts/create.php';
        break;
    case '/posts/view.php':
        include 'posts/view.php';
        break;
    case '/subscribe.php':
        include 'subscribe.php';
        break;

    case '/unsubscribe.php':
        include 'unsubscribe.php';
        break;
    case '/subscriptions.php':
        include 'subscriptions.php';
        break;
    case '/users.php':
        include 'users.php';
        break;
    case '/comments/add.php':
        include 'comments/add.php';
        break;

    case '/comments/delete.php':
        include 'comments/delete.php';
        break;
    default:
        if (strpos($path, '/posts/view.php') === 0) {
            include 'posts/view.php';
        } else {
            http_response_code(404);
            echo "Страница не найдена: " . htmlspecialchars($path);
        }
        break;
}
