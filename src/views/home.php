<?php
require_once __DIR__ . '/../utils/Database.php';

$db = Database::getInstance();
$isConnected = $db->isConnected();
$tables = [];

if ($isConnected) {
    try {
        $tables = $db->getConnection()->query(
            "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'"
        )->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Table query error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная - Мой Блог</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: #f5f5f5;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .nav {
            background: white;
            padding: 1rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            justify-content: center;
            gap: 2rem;
        }
        .nav a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .nav a:hover {
            background: #f0f0f0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        .status-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        .post-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        .post-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .post-card:hover {
            transform: translateY(-5px);
        }
        .btn {
            background: #667eea;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #5a67d8;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>📝 Мой Блог</h1>
        <p>Делитесь своими мыслями и идеями с миром</p>
    </div>

    <div class="nav">
        <a href="/">Главная</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="/posts/create.php">✏️ Новый пост</a>
            <a href="/logout.php">🚪 Выйти (<?= htmlspecialchars($_SESSION['username']) ?>)</a>
        <?php else: ?>
            <a href="/login.php">🔑 Вход</a>
            <a href="/register.php">👤 Регистрация</a>
        <?php endif; ?>
    </div>

    <div class="container">
        <!-- Статус базы данных
         <div class="status-card">
            <h2>Статус системы</h2>
            <?php if ($isConnected): ?>
                <p class="success">✅ Подключение к PostgreSQL успешно!</p>
                <?php if (count($tables) === 0): ?>
                    <p class="warning">⚠️ Таблицы не найдены!
                        <a href="/init_database.php" style="color: orange; margin-left: 10px;">
                            🚀 Инициализировать базу данных
                        </a>
                    </p>
                <?php else: ?>
                    <p>📊 Найдено таблиц: <?= count($tables) ?></p>
                <?php endif; ?>
            <?php else: ?>
                <p class="error">❌ Не удалось подключиться к базе данных</p>
            <?php endif; ?>
        </div>
        -->
        <!-- Контент главной страницы -->
        <div class="status-card">
            <h2>Добро пожаловать в блог!</h2>

            <?php if (!isset($_SESSION['user_id'])): ?>
                <p>Присоединяйтесь к нашему сообществу! Здесь вы можете:</p>
                <ul>
                    <li>📝 Публиковать свои мысли и идеи</li>
                    <li>👥 Подписываться на других авторов</li>
                    <li>🏷️ Организовывать посты с помощью тегов</li>
                    <li>💬 Обсуждать посты в комментариях</li>
                </ul>
                <div style="margin-top: 1.5rem;">
                    <a href="/register.php" class="btn">Начать сейчас</a>
                    <a href="/login.php" style="margin-left: 1rem; color: #667eea;">Уже есть аккаунт?</a>
                </div>
            <?php else: ?>
                <p>Добро пожаловать, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>! 🎉</p>
                <p>Рады видеть вас снова! Что бы вы хотели сделать?</p>
                <div style="margin-top: 1.5rem;">
                    <a href="/posts/create.php" class="btn">📝 Написать новый пост</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Последние посты -->
        <?php if ($isConnected && count($tables) > 0): ?>
            <div class="status-card">
                <h2>📋 Последние публичные посты</h2>
                <?php
                try {
                    $stmt = $db->getConnection()->query("
                        SELECT p.*, u.username
                        FROM posts p
                        JOIN users u ON p.user_id = u.id
                        WHERE p.visibility = 'public'
                        ORDER BY p.created_at DESC
                        LIMIT 6
                    ");
                    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if ($posts): ?>
                        <div class="post-grid">
                            <?php foreach ($posts as $post): ?>
                                <div class="post-card">
                                    <h3><?= htmlspecialchars($post['title']) ?></h3>
                                    <p><?= nl2br(htmlspecialchars(substr($post['content'], 0, 150))) ?>...</p>
                                    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #eee;">
                                        <small>👤 <?= htmlspecialchars($post['username']) ?></small><br>
                                        <small>📅 <?= date('d.m.Y H:i', strtotime($post['created_at'])) ?></small>
                                    </div>
                                    <a href="/posts/view.php?id=<?= $post['id'] ?>"
                                       style="display: inline-block; margin-top: 1rem; color: #667eea;">
                                        Читать далее →
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>Пока нет публичных постов. Будьте первым!</p>
                    <?php endif;

                } catch (PDOException $e) {
                    echo "<p class='error'>Ошибка загрузки постов: " . htmlspecialchars($e->getMessage()) . "</p>";
                }
                ?>
            </div>
        <?php endif; ?>

        <!-- Статистика -->
        <?php if ($isConnected && count($tables) > 0): ?>
            <div class="status-card">
                <h2>📊 Статистика</h2>
                <?php
                try {
                    $usersCount = $db->getConnection()->query("SELECT COUNT(*) FROM users")->fetchColumn();
                    $postsCount = $db->getConnection()->query("SELECT COUNT(*) FROM posts")->fetchColumn();
                    $publicPosts = $db->getConnection()->query("SELECT COUNT(*) FROM posts WHERE visibility = 'public'")->fetchColumn();
                    ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                            <h3>👥 <?= $usersCount ?></h3>
                            <p>Пользователей</p>
                        </div>
                        <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                            <h3>📝 <?= $postsCount ?></h3>
                            <p>Всего постов</p>
                        </div>
                        <div style="text-align: center; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                            <h3>🌐 <?= $publicPosts ?></h3>
                            <p>Публичных постов</p>
                        </div>
                    </div>
                    <?php
                } catch (PDOException $e) {
                    // Ignore stats errors
                }
                ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
