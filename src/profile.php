<?php
session_start();
require_once __DIR__ . '/utils/Database.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Post.php';
require_once __DIR__ . '/models/Subscription.php';
require_once __DIR__ . '/utils/helpers.php';

$userId = $_GET['user_id'] ?? null;
if (!$userId) {
    header('Location: /');
    exit;
}

try {
    $userModel = new User();
    $postModel = new Post();
    $subscriptionModel = new Subscription();

    $user = $userModel->findById($userId);
    if (!$user) {
        die("Пользователь не найден");
    }

    $posts = $postModel->getPostsByUser($userId, $_SESSION['user_id'] ?? null);
    $subscribersCount = $subscriptionModel->getSubscribersCount($userId);
    $subscriptionsCount = $subscriptionModel->getSubscriptionsCount($userId);

    $content = '
    <div style="max-width: 800px; margin: 0 auto;">
        <a href="/" style="color: #667eea; text-decoration: none; margin-bottom: 1rem; display: inline-block;">← На главную</a>

        <div style="background: white; padding: 30px; border-radius: 10px; margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div>
                    <h1 style="margin: 0;">👤 ' . htmlspecialchars($user['username']) . '</h1>
                    <p style="color: #666; margin: 5px 0 0 0;">' . htmlspecialchars($user['email']) . '</p>
                    <p style="color: #666; margin: 5px 0 0 0;">
                        Зарегистрирован: ' . date('d.m.Y', strtotime($user['created_at'])) . '
                    </p>
                </div>
                ' . (isset($_SESSION['user_id']) ? getSubscribeButton($userId, $_SESSION['user_id']) : '') . '
            </div>

            <div style="display: flex; gap: 30px; margin-bottom: 20px;">
                <a href="/subscriptions.php?user_id=' . $userId . '&type=subscribers"
                   style="text-decoration: none; color: #333;">
                    <div style="text-align: center;">
                        <div style="font-size: 24px; font-weight: bold;">' . $subscribersCount . '</div>
                        <div>Подписчики</div>
                    </div>
                </a>
                <a href="/subscriptions.php?user_id=' . $userId . '&type=subscriptions"
                   style="text-decoration: none; color: #333;">
                    <div style="text-align: center;">
                        <div style="font-size: 24px; font-weight: bold;">' . $subscriptionsCount . '</div>
                        <div>Подписки</div>
                    </div>
                </a>
                <div style="text-align: center;">
                    <div style="font-size: 24px; font-weight: bold;">' . count($posts) . '</div>
                    <div>Посты</div>
                </div>
            </div>
        </div>

        <h2>📝 Посты пользователя</h2>';

    if (!empty($posts)) {
        foreach ($posts as $post) {
            $content .= '
            <div style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 15px;">
                <h3 style="margin: 0 0 10px 0;">
                    <a href="/posts/view.php?id=' . $post['id'] . '" style="color: #333; text-decoration: none;">
                        ' . htmlspecialchars($post['title']) . '
                    </a>
                </h3>
                <p style="color: #666; margin: 0 0 10px 0;">' .
                nl2br(htmlspecialchars(substr($post['content'], 0, 200))) . '...</p>
                <div style="color: #999; font-size: 14px;">
                    📅 ' . date('d.m.Y H:i', strtotime($post['created_at'])) . ' |
                    👁️ ' . htmlspecialchars($post['visibility']) . '
                </div>
            </div>';
        }
    } else {
        $content .= '
        <div style="text-align: center; padding: 40px; background: white; border-radius: 10px;">
            <p style="color: #666;">У пользователя пока нет постов</p>
        </div>';
    }

    $content .= '</div>';

} catch (Exception $e) {
    $content = '<div class="error">Ошибка: ' . $e->getMessage() . '</div>';
}

include __DIR__ . '/views/layout.php';
