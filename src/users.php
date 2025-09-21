<?php
session_start();
require_once __DIR__ . '/utils/Database.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/models/Subscription.php';
require_once __DIR__ . '/utils/helpers.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

try {
    $userModel = new User();
    $subscriptionModel = new Subscription();

    // Получаем всех пользователей кроме текущего
    $users = $userModel->getAllUsersExcept($_SESSION['user_id']);

    $content = '
    <div style="max-width: 800px; margin: 0 auto;">
        <a href="/" style="color: #667eea; text-decoration: none; margin-bottom: 1rem; display: inline-block;">← На главную</a>

        <h2>🔍 Поиск пользователей</h2>

        <div style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px;">
            <input type="text" placeholder="Поиск по имени пользователя..."
                   style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"
                   onkeyup="filterUsers(this.value)">
        </div>';

    if (!empty($users)) {
        $content .= '<div id="users-list">';
        foreach ($users as $user) {
            $isSubscribed = $subscriptionModel->isSubscribed($_SESSION['user_id'], $user['id']);

            $content .= '
            <div class="user-card" style="background: white; padding: 15px; border-radius: 10px; margin-bottom: 10px;
                                         display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong>👤 ' . htmlspecialchars($user['username']) . '</strong>
                    <div style="color: #666; font-size: 14px;">
                        Зарегистрирован: ' . date('d.m.Y', strtotime($user['created_at'])) . '
                    </div>
                </div>
                <div>
                    ' . ($isSubscribed ? '
                    <a href="/unsubscribe.php?user_id=' . $user['id'] . '"
                       style="background: #dc3545; color: white; padding: 8px 16px;
                              text-decoration: none; border-radius: 5px; font-size: 14px;"
                       onclick="return confirm(\'Отписаться от этого пользователя?\')">
                       ❌ Отписаться
                    </a>
                    ' : '
                    <a href="/subscribe.php?user_id=' . $user['id'] . '"
                       style="background: #28a745; color: white; padding: 8px 16px;
                              text-decoration: none; border-radius: 5px; font-size: 14px;">
                       ✅ Подписаться
                    </a>
                    ') . '
                    <a href="/profile.php?user_id=' . $user['id'] . '"
                       style="margin-left: 10px; color: #667eea; text-decoration: none;">
                       👀 Профиль
                    </a>
                </div>
            </div>';
        }
        $content .= '</div>';
    } else {
        $content .= '
        <div style="text-align: center; padding: 40px; background: white; border-radius: 10px;">
            <p style="color: #666;">Других пользователей не найдено</p>
        </div>';
    }

    $content .= '
        <script>
        function filterUsers(searchTerm) {
            const users = document.querySelectorAll(".user-card");
            users.forEach(user => {
                const username = user.querySelector("strong").textContent.toLowerCase();
                if (username.includes(searchTerm.toLowerCase())) {
                    user.style.display = "flex";
                } else {
                    user.style.display = "none";
                }
            });
        }
        </script>
    </div>';

} catch (Exception $e) {
    $content = '<div class="error">Ошибка: ' . $e->getMessage() . '</div>';
}

include __DIR__ . '/views/layout.php';
