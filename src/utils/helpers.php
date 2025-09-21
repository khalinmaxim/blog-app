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
