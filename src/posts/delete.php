<?php
session_start();
require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../models/Post.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

$error = '';
$success = '';
$post = null;

// Получаем ID поста из URL
$postId = $_GET['id'] ?? null;

if (!$postId) {
    $error = "ID поста не указан";
} else {
    try {
        $postModel = new Post();
        $post = $postModel->getPostWithOwnerCheck($postId, $_SESSION['user_id']);

        if (!$post) {
            $error = "Пост не найден или у вас нет прав для его удаления";
        }

        // Обработка подтверждения удаления
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $confirm = $_POST['confirm'] ?? '';

            if ($confirm === 'yes') {
                $result = $postModel->delete($postId, $_SESSION['user_id']);

                if ($result) {
                    $success = "Пост успешно удален!";
                    // Перенаправляем на главную через 2 секунды
                    header('Refresh: 2; URL=/');
                } else {
                    $error = "Ошибка при удалении поста";
                }
            } else {
                // Пользователь отменил удаление
                header('Location: /posts/view.php?id=' . $postId);
                exit;
            }
        }

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// HTML содержимое
$content = '
<div style="max-width: 600px; margin: 0 auto;">
    <a href="/" style="color: #667eea; text-decoration: none; margin-bottom: 1rem; display: inline-block;">← На главную</a>

    ' . ($error ? '<div class="error">' . $error . '</div>' : '') . '
    ' . ($success ? '<div class="success">' . $success . '</div>' : '') . '

    ' . ($post && !$success ? '
    <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 20px; border-radius: 10px; margin: 20px 0;">
        <h2 style="color: #856404; margin-top: 0;">🗑️ Подтверждение удаления</h2>

        <p style="margin-bottom: 1rem;">Вы уверены, что хотите удалить этот пост?</p>

        <div style="background: white; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <h3 style="margin-top: 0;">' . htmlspecialchars($post['title']) . '</h3>
            <p style="color: #666; margin: 0;">' . nl2br(htmlspecialchars(substr($post['content'], 0, 200))) . '...</p>
            <div style="margin-top: 10px; font-size: 14px; color: #999;">
                Создан: ' . date('d.m.Y H:i', strtotime($post['created_at'])) . ' |
                Видимость: ' . htmlspecialchars($post['visibility']) . '
            </div>
        </div>

        <form method="post">
            <div style="display: flex; gap: 10px;">
                <button type="submit" name="confirm" value="yes"
                        style="background: #dc3545; padding: 10px 20px; border: none; border-radius: 5px; color: white; cursor: pointer;">
                    ✅ Да, удалить
                </button>
                <button type="submit" name="confirm" value="no"
                        style="background: #6c757d; padding: 10px 20px; border: none; border-radius: 5px; color: white; cursor: pointer;">
                    ❌ Нет, отмена
                </button>
            </div>
        </form>
    </div>
    ' : '') . '

    ' . (!$post && !$error && !$success ? '
    <div style="text-align: center; padding: 40px;">
        <h2>Пост не найден</h2>
        <p>Возможно, он был уже удален или у вас нет прав для его удаления.</p>
        <a href="/" style="color: #667eea;">Вернуться на главную</a>
    </div>
    ' : '') . '
</div>
';

// Включаем layout
include __DIR__ . '/../views/layout.php';
