<?php
require_once __DIR__ . '/../models/Subscription.php';

class SubscriptionController {
    private $subscriptionModel;

    public function __construct() {
        $this->subscriptionModel = new Subscription();
    }

    // Подписаться на пользователя
    public function subscribe() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login.php');
            exit;
        }

        $targetId = $_GET['user_id'] ?? null;
        if (!$targetId) {
            $_SESSION['error'] = "ID пользователя не указан";
            header('Location: /');
            exit;
        }

        try {
            $success = $this->subscriptionModel->subscribe($_SESSION['user_id'], $targetId);
            if ($success) {
                $_SESSION['success'] = "Вы успешно подписались на пользователя!";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
        exit;
    }

    // Отписаться от пользователя
    public function unsubscribe() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login.php');
            exit;
        }

        $targetId = $_GET['user_id'] ?? null;
        if (!$targetId) {
            $_SESSION['error'] = "ID пользователя не указан";
            header('Location: /');
            exit;
        }

        try {
            $success = $this->subscriptionModel->unsubscribe($_SESSION['user_id'], $targetId);
            if ($success) {
                $_SESSION['success'] = "Вы отписались от пользователя";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
        exit;
    }

    // Страница подписок пользователя
    public function subscriptions() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login.php');
            exit;
        }

        $userId = $_GET['user_id'] ?? $_SESSION['user_id'];
        $type = $_GET['type'] ?? 'subscribers'; // subscribers или subscriptions

        try {
            if ($type === 'subscriptions') {
                $data = $this->subscriptionModel->getSubscriptions($userId);
                $title = "Подписки";
            } else {
                $data = $this->subscriptionModel->getSubscribers($userId);
                $title = "Подписчики";
            }

            // Получаем информацию о пользователе
            $userModel = new User();
            $user = $userModel->findById($userId);

            $content = '
            <div style="max-width: 800px; margin: 0 auto;">
                <a href="/" style="color: #667eea; text-decoration: none; margin-bottom: 1rem; display: inline-block;">← На главную</a>

                <h2>👥 ' . $title . ' пользователя ' . htmlspecialchars($user['username']) . '</h2>

                <div style="display: flex; gap: 20px; margin-bottom: 20px;">
                    <a href="/subscriptions.php?user_id=' . $userId . '&type=subscribers"
                       style="padding: 10px 20px; background: ' . ($type === 'subscribers' ? '#667eea' : '#f8f9fa') . ';
                              color: ' . ($type === 'subscribers' ? 'white' : '#333') . ';
                              text-decoration: none; border-radius: 5px;">
                       Подписчики (' . $this->subscriptionModel->getSubscribersCount($userId) . ')
                    </a>
                    <a href="/subscriptions.php?user_id=' . $userId . '&type=subscriptions"
                       style="padding: 10px 20px; background: ' . ($type === 'subscriptions' ? '#667eea' : '#f8f9fa') . ';
                              color: ' . ($type === 'subscriptions' ? 'white' : '#333') . ';
                              text-decoration: none; border-radius: 5px;">
                       Подписки (' . $this->subscriptionModel->getSubscriptionsCount($userId) . ')
                    </a>
                </div>';

            if (!empty($data)) {
                $content .= '<div style="background: white; border-radius: 10px; padding: 20px;">';
                foreach ($data as $item) {
                    $content .= '
                    <div style="display: flex; justify-content: space-between; align-items: center;
                                padding: 15px; border-bottom: 1px solid #eee;">
                        <div>
                            <strong>👤 ' . htmlspecialchars($item['username']) . '</strong>
                            <div style="color: #666; font-size: 14px;">
                                Подписан: ' . date('d.m.Y', strtotime($item['created_at'])) . '
                            </div>
                        </div>
                        <a href="/profile.php?user_id=' . $item['id'] . '"
                           style="color: #667eea; text-decoration: none;">
                            Профиль →
                        </a>
                    </div>';
                }
                $content .= '</div>';
            } else {
                $content .= '
                <div style="text-align: center; padding: 40px; background: white; border-radius: 10px;">
                    <p style="color: #666; font-size: 18px;">' .
                    ($type === 'subscribers' ? 'Нет подписчиков' : 'Нет подписок') . '</p>
                </div>';
            }

            $content .= '</div>';

            echo $content;

        } catch (Exception $e) {
            echo '<div class="error">Ошибка: ' . $e->getMessage() . '</div>';
        }
    }
}
