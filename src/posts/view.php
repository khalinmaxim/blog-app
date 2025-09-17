<?php
session_start();
require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../models/Post.php';

$db = Database::getInstance();

if (!$db->isConnected()) {
    die("Нет подключения к базе данных");
}

$postId = $_GET['id'] ?? null;

if (!$postId) {
    die("Пост не указан");
}

try {
    $postModel = new Post();
    $post = $postModel->findById($postId);

    if (!$post) {
        die("Пост не найден");
    }

    // Проверяем доступ к посту
    $canView = true;
    if ($post['visibility'] === 'private') {
        $canView = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id'];
    } elseif ($post['visibility'] === 'request') {
        // Здесь можно добавить логику запроса доступа
        $canView = isset($_SESSION['user_id']);
    }

    if (!$canView) {
        die("У вас нет доступа к этому посту");
    }

} catch (PDOException $e) {
    die("Ошибка загрузки поста: " . $e->getMessage());
}

// HTML содержимое
$content = '
<div style="max-width: 800px; margin: 0 auto;">
    <a href="/" style="color: #667eea; text-decoration: none; margin-bottom: 1rem; display: inline-block;">← На главную</a>

    <h1>' . htmlspecialchars($post['title']) . '</h1>

    <div style="color: #666; margin-bottom: 2rem;">
        <strong>👤 ' . htmlspecialchars($post['username']) . '</strong> |
        <span>📅 ' . date('d.m.Y H:i', strtotime($post['created_at'])) . '</span> |
        <span>👁️ ' . htmlspecialchars($post['visibility']) . '</span>
    </div>

    <div style="line-height: 1.6; font-size: 16px; background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        ' . nl2br(htmlspecialchars($post['content'])) . '
    </div>

    ' . (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id'] ? '
    <div style="margin-top: 2rem;">
        <a href="/posts/edit.php?id=' . $post['id'] . '" style="color: #667eea; margin-right: 1rem;">✏️ Редактировать</a>
        <a href="/posts/delete.php?id=' . $post['id'] . '" style="color: #dc3545;" onclick="return confirm(\'Удалить этот пост?\')">🗑️ Удалить</a>
    </div>
    ' : '') . '
</div>
';

// Включаем layout
include __DIR__ . '/../views/layout.php';
