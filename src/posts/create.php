<?php
session_start();
require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Post.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

$error = '';
$success = '';

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $visibility = $_POST['visibility'];
    $tags = isset($_POST['tags']) ? array_map('trim', explode(',', $_POST['tags'])) : [];

    if (empty($title) || empty($content)) {
        $error = "Заголовок и содержание обязательны";
    } else {
        try {
            $postModel = new Post();
            $postId = $postModel->create($_SESSION['user_id'], $title, $content, $visibility, $tags);

            if ($postId) {
                $success = "Пост успешно создан!";
                // Очищаем форму
                $title = $content = $tags = '';
                $visibility = 'public';
            } else {
                $error = "Ошибка при создании поста";
            }
        } catch (Exception $e) {
            $error = "Ошибка: " . $e->getMessage();
        }
    }
}

// HTML содержимое
$content = '
<h2>📝 Создать новый пост</h2>

<form method="post">
    <div>
        <label>Заголовок:</label>
        <input type="text" name="title" value="' . (isset($title) ? htmlspecialchars($title) : '') . '" required placeholder="Введите заголовок поста">
    </div>

    <div>
        <label>Содержание:</label>
        <textarea name="content" rows="10" required placeholder="Напишите содержание вашего поста">' . (isset($content) ? htmlspecialchars($content) : '') . '</textarea>
    </div>

    <div>
        <label>Видимость:</label>
        <select name="visibility">
            <option value="public" ' . ((isset($visibility) && $visibility === 'public') ? 'selected' : '') . '>🌐 Публичный</option>
            <option value="private" ' . ((isset($visibility) && $visibility === 'private') ? 'selected' : '') . '>🔒 Приватный</option>
            <option value="request" ' . ((isset($visibility) && $visibility === 'request') ? 'selected' : '') . '>🔐 По запросу</option>
        </select>
    </div>

    <div>
        <label>Теги (через запятую):</label>
        <input type="text" name="tags" value="' . (isset($tags) && is_array($tags) ? htmlspecialchars(implode(', ', $tags)) : (isset($tags) ? htmlspecialchars($tags) : '')) . '" placeholder="php, docker, блог">
    </div>

    <button type="submit">📤 Опубликовать</button>
</form>

<p><a href="/">← На главную</a></p>
';

// Включаем layout
include __DIR__ . '/../views/layout.php';
