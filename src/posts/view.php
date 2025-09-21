<?php
session_start();
require_once __DIR__ . '/../utils/Database.php';
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/Comment.php';

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
    $post = $postModel->getPostWithTags($postId);

    if (!$post) {
        die("Пост не найден");
    }

    // Проверяем доступ к посту
    $canView = true;
    if ($post['visibility'] === 'private') {
        $canView = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id'];
    } elseif ($post['visibility'] === 'request') {
        $canView = isset($_SESSION['user_id']);
    }

    if (!$canView) {
        die("У вас нет доступа к этому посту");
    }

    // Получаем комментарии для поста
    try {
        $commentModel = new Comment();
        $comments = $commentModel->getByPostId($postId);
        $commentCount = $commentModel->getCountByPostId($postId);
    } catch (Exception $e) {
        $comments = [];
        $commentCount = 0;
        error_log("Comments error: " . $e->getMessage());
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
        <strong>👤 <a href="/profile.php?user_id=' . $post['user_id'] . '"
                   style="color: #667eea; text-decoration: none;">
                   ' . htmlspecialchars($post['username']) . '
               </a>
        </strong> |
        <span>📅 ' . date('d.m.Y H:i', strtotime($post['created_at'])) . '</span> |
        <span>👁️ ' . htmlspecialchars($post['visibility']) . '</span>';

// Добавляем теги, если они есть
if (isset($post['tags']) && is_array($post['tags']) && !empty($post['tags'])) {
    $content .= '| <span>🏷️ ' . implode(', ', array_map('htmlspecialchars', $post['tags'])) . '</span>';
}

$content .= '
    </div>

    <div style="line-height: 1.6; font-size: 16px; background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        ' . nl2br(htmlspecialchars($post['content'])) . '
    </div>

    ' . (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['user_id'] ? '
    <div style="margin-top: 2rem; display: flex; gap: 10px;">
        <a href="/posts/edit.php?id=' . $post['id'] . '"
           style="background: #28a745; color: white; padding: 8px 16px; text-decoration: none; border-radius: 5px;">
           ✏️ Редактировать
        </a>
        <a href="/posts/delete.php?id=' . $post['id'] . '"
           style="background: #dc3545; color: white; padding: 8px 16px; text-decoration: none; border-radius: 5px;"
           onclick="return confirm(\'Вы уверены, что хотите удалить этот пост?\\n\\\"' . htmlspecialchars(addslashes($post['title'])) . '\\\"\')">
           🗑️ Удалить
        </a>
    </div>
    ' : '') . '

    <!-- Блок комментариев -->
    <div style="margin-top: 3rem;">
        <h3>💬 Комментарии (' . $commentCount . ')</h3>

        <!-- Форма добавления комментария -->
        ' . (isset($_SESSION['user_id']) ? '
        <div style="background: white; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <form method="post" action="/comments/add.php">
                <input type="hidden" name="post_id" value="' . $post['id'] . '">
                <div>
                    <textarea name="content" rows="3"
                              placeholder="Напишите ваш комментарий..."
                              style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"
                              required></textarea>
                </div>
                <div style="margin-top: 10px;">
                    <button type="submit"
                            style="background: #667eea; color: white; padding: 8px 16px; border: none; border-radius: 5px; cursor: pointer;">
                        📤 Отправить комментарий
                    </button>
                </div>
            </form>
        </div>
        ' : '
        <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem; text-align: center;">
            <p>💡 <a href="/login.php" style="color: #667eea;">Войдите</a> чтобы оставить комментарий</p>
        </div>
        ') . '

        <!-- Список комментариев -->
        <div style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            ' . (!empty($comments) ? '
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                ' . implode('', array_map(function($comment) {
                    return '
                    <div style="padding: 1rem; border-bottom: 1px solid #eee;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div style="flex: 1;">
                                <strong>👤 <a href="/profile.php?user_id=' . $comment['user_id'] . '"
                                           style="color: #667eea; text-decoration: none;">
                                           ' . htmlspecialchars($comment['username']) . '
                                       </a>
                                </strong>
                                <div style="color: #666; font-size: 14px; margin-top: 5px;">
                                    ' . nl2br(htmlspecialchars($comment['content'])) . '
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div style="color: #999; font-size: 12px;">
                                    ' . date('d.m.Y H:i', strtotime($comment['created_at'])) . '
                                </div>
                                ' . (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $comment['user_id'] ? '
                                <div style="margin-top: 5px;">
                                    <a href="/comments/delete.php?id=' . $comment['id'] . '"
                                       style="color: #dc3545; font-size: 12px; text-decoration: none;"
                                       onclick="return confirm(\'Удалить этот комментарий?\')">
                                        🗑️ Удалить
                                    </a>
                                </div>
                                ' : '') . '
                            </div>
                        </div>
                    </div>';
                }, $comments)) . '
            </div>
            ' : '
            <div style="text-align: center; padding: 2rem; color: #666;">
                <p>Пока нет комментариев. Будьте первым!</p>
            </div>
            ') . '
        </div>
    </div>
</div>
';

// Включаем layout
include __DIR__ . '/../views/layout.php';
?>
