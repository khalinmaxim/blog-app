<?php
session_start();
require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/User.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

$error = '';
$success = '';
$post = null;
$tagsInput = '';

// Получаем ID поста из URL
$postId = $_GET['id'] ?? null;

if (!$postId) {
    $error = "ID поста не указан";
} else {
    try {
        $postModel = new Post();
        $post = $postModel->getPostWithOwnerCheck($postId, $_SESSION['user_id']);

        if (!$post) {
            $error = "Пост не найден или у вас нет прав для его редактирования";
        } else {
            // Получаем теги поста
            $tags = $postModel->getPostTags($postId);
            $tagsInput = implode(', ', $tags);

            // Обработка формы редактирования
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $title = trim($_POST['title']);
                $content = trim($_POST['content']);
                $visibility = $_POST['visibility'];
                $tagsInput = trim($_POST['tags'] ?? '');

                // Преобразуем теги в массив
                $tags = !empty($tagsInput) ? array_map('trim', explode(',', $tagsInput)) : [];

                if (empty($title) || empty($content)) {
                    $error = "Заголовок и содержание обязательны";
                } else {
                    $result = $postModel->update($postId, $_SESSION['user_id'], $title, $content, $visibility, $tags);

                    if ($result) {
                        $success = "Пост успешно обновлен!";

                        // Обновляем данные для отображения
                        $post['title'] = $title;
                        $post['content'] = $content;
                        $post['visibility'] = $visibility;

                        // Обновляем теги
                        $tags = $postModel->getPostTags($postId);
                        $tagsInput = implode(', ', $tags);

                        // Перенаправляем на просмотр поста через 2 секунды
                        header('Refresh: 2; URL=/posts/view.php?id=' . $postId);
                    } else {
                        $error = "Ошибка при обновлении поста";
                    }
                }
            }
        }

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// HTML содержимое
$content = '
<div style="max-width: 800px; margin: 0 auto;">
    <a href="/posts/view.php?id=' . $postId . '" style="color: #667eea; text-decoration: none; margin-bottom: 1rem; display: inline-block;">← Назад к посту</a>

    ' . ($error ? '<div class="error">' . $error . '</div>' : '') . '
    ' . ($success ? '<div class="success">' . $success . '</div>' : '') . '

    ' . ($post ? '
    <h2>✏️ Редактирование поста</h2>

    <form method="post">
        <div>
            <label>Заголовок:</label>
            <input type="text" name="title" value="' . htmlspecialchars($post['title']) . '" required
                   placeholder="Введите заголовок поста">
        </div>

        <div>
            <label>Содержание:</label>
            <textarea name="content" rows="12" required
                      placeholder="Напишите содержание вашего поста">' . htmlspecialchars($post['content']) . '</textarea>
        </div>

        <div>
            <label>Видимость:</label>
            <select name="visibility">
                <option value="public" ' . ($post['visibility'] === 'public' ? 'selected' : '') . '>🌐 Публичный</option>
                <option value="private" ' . ($post['visibility'] === 'private' ? 'selected' : '') . '>🔒 Приватный</option>
                <option value="request" ' . ($post['visibility'] === 'request' ? 'selected' : '') . '>🔐 По запросу</option>
            </select>
            <small style="color: #666;">
                🌐 Публичный - виден всем<br>
                🔒 Приватный - виден только вам<br>
                🔐 По запросу - виден после одобрения вашего запроса
            </small>
        </div>

        <div>
            <label>Теги (через запятую):</label>
            <input type="text" name="tags" value="' . htmlspecialchars($tagsInput) . '"
                   placeholder="php, docker, блог, программирование">
            <small style="color: #666;">Например: php, docker, блог, программирование. Теги помогают categorize ваш пост.</small>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 20px;">
            <button type="submit" style="background: #28a745; padding: 12px 24px;">
                💾 Сохранить изменения
            </button>
            <a href="/posts/view.php?id=' . $postId . '"
               style="background: #6c757d; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px;">
               ❌ Отмена
            </a>
        </div>
    </form>

    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
        <h3>📊 Информация о посте</h3>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px;">
            <p><strong>ID поста:</strong> ' . $post['id'] . '</p>
            <p><strong>Создан:</strong> ' . date('d.m.Y H:i', strtotime($post['created_at'])) . '</p>
            <p><strong>Обновлен:</strong> ' . ($post['updated_at'] !== $post['created_at'] ?
                date('d.m.Y H:i', strtotime($post['updated_at'])) : 'еще не обновлялся') . '</p>
            <p><strong>Автор:</strong> ' . htmlspecialchars($post['username']) . '</p>
        </div>
    </div>
    ' : '
    <div style="text-align: center; padding: 40px;">
        <h2>Пост не найден</h2>
        <p>Возможно, он был удален или у вас нет прав для его редактирования.</p>
        <a href="/" style="color: #667eea;">Вернуться на главную</a>
    </div>
    ') . '
</div>
';

// Включаем layout
include __DIR__ . '/../views/layout.php';
