<?php
// Скрипт для инициализации базы данных вручную
require_once 'utils/Database.php';

echo "<h1>Инициализация базы данных</h1>";

$db = Database::getInstance();

if (!$db->isConnected()) {
    die("<p style='color: red;'>❌ Нет подключения к базе данных</p>");
}

try {
    // SQL для создания таблиц
    $sql = "
    CREATE TABLE IF NOT EXISTS users (
        id SERIAL PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS posts (
        id SERIAL PRIMARY KEY,
        user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        visibility VARCHAR(20) DEFAULT 'public' CHECK (visibility IN ('public', 'private', 'request')),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS tags (
        id SERIAL PRIMARY KEY,
        name VARCHAR(50) UNIQUE NOT NULL
    );

    CREATE TABLE IF NOT EXISTS post_tags (
        post_id INTEGER REFERENCES posts(id) ON DELETE CASCADE,
        tag_id INTEGER REFERENCES tags(id) ON DELETE CASCADE,
        PRIMARY KEY (post_id, tag_id)
    );

    CREATE TABLE IF NOT EXISTS subscriptions (
        subscriber_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
        target_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (subscriber_id, target_id)
    );

    CREATE TABLE IF NOT EXISTS comments (
        id SERIAL PRIMARY KEY,
        post_id INTEGER REFERENCES posts(id) ON DELETE CASCADE,
        user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
        content TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS post_requests (
        id SERIAL PRIMARY KEY,
        post_id INTEGER REFERENCES posts(id) ON DELETE CASCADE,
        user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
        status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'approved', 'rejected')),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(post_id, user_id)
    );
    ";

    // Выполняем SQL
    $db->getConnection()->exec($sql);
    echo "<p style='color: green;'>✅ Таблицы созданы успешно!</p>";

    // Добавляем тестовые данные
    $testData = "
    INSERT INTO users (username, email, password) VALUES
    ('testuser', 'test@example.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
    ON CONFLICT (username) DO NOTHING;

    INSERT INTO posts (user_id, title, content, visibility) VALUES
    (1, 'Первый пост', 'Это мой первый пост в блоге!', 'public'),
    (1, 'Приватный пост', 'Этот пост виден только мне', 'private'),
    (1, 'Пост по запросу', 'Этот пост виден только тем, у кого есть доступ', 'request')
    ON CONFLICT DO NOTHING;

    INSERT INTO tags (name) VALUES
    ('php'), ('docker'), ('postgresql'), ('блог'), ('тест')
    ON CONFLICT (name) DO NOTHING;

    INSERT INTO post_tags (post_id, tag_id) VALUES
    (1, 1), (1, 4), (1, 5),
    (2, 2), (2, 3),
    (3, 1), (3, 3)
    ON CONFLICT DO NOTHING;
    ";

    $db->getConnection()->exec($testData);
    echo "<p style='color: green;'>✅ Тестовые данные добавлены!</p>";

    // Проверяем созданные таблицы
    $tables = $db->getConnection()->query(
        "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'"
    )->fetchAll(PDO::FETCH_ASSOC);

    echo "<h3>Созданные таблицы:</h3>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>" . htmlspecialchars($table['table_name']) . "</li>";
    }
    echo "</ul>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Ошибка: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<p><a href='/'>Вернуться на главную</a></p>";
