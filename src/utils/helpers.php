<?php
function redirect($url) {
    header("Location: $url");
    exit;
}

function sanitize($data) {
    return htmlspecialchars(trim($data));
}

function formatTags($tags) {
    if (is_array($tags)) {
        return $tags;
    }

    if (is_string($tags)) {
        // Обрабатываем PostgreSQL array format: {tag1,tag2,tag3}
        $tags = trim($tags, '{}');
        return $tags ? array_map('trim', explode(',', $tags)) : [];
    }

    return [];
}

function displayTags($tags) {
    $formattedTags = formatTags($tags);
    if (!empty($formattedTags)) {
        return '| <span>🏷️ ' . implode(', ', array_map('htmlspecialchars', $formattedTags)) . '</span>';
    }
    return '';
}

function getSubscribeButton($targetUserId, $currentUserId = null) {
    if (!$currentUserId || $targetUserId == $currentUserId) {
        return '';
    }

    $subscription = new Subscription();
    $isSubscribed = $subscription->isSubscribed($currentUserId, $targetUserId);

    if ($isSubscribed) {
        return '
        <a href="/unsubscribe.php?user_id=' . $targetUserId . '"
           style="background: #dc3545; color: white; padding: 8px 16px;
                  text-decoration: none; border-radius: 5px; font-size: 14px;"
           onclick="return confirm(\'Отписаться от этого пользователя?\')">
           ❌ Отписаться
        </a>';
    } else {
        return '
        <a href="/subscribe.php?user_id=' . $targetUserId . '"
           style="background: #28a745; color: white; padding: 8px 16px;
                  text-decoration: none; border-radius: 5px; font-size: 14px;">
           ✅ Подписаться
        </a>';
    }
}
