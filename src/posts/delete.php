<?php
session_start();
require_once __DIR__ . '/../utils/Database.php';

// Заглушка для удаления
$content = '
<h2>🗑️ Удаление поста</h2>
<p>Функция удаления в разработке...</p>
<p><a href="/">← На главную</a></p>
';

include __DIR__ . '/../views/layout.php';
