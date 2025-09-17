<h2>Добро пожаловать в блог!</h2>

<?php if (!isset($_SESSION['user_id'])): ?>
    <p>Пожалуйста, <a href="/login.php">войдите</a> или <a href="/register.php">зарегистрируйтесь</a> чтобы начать.</p>
<?php else: ?>
    <p>Добро пожаловать, <?= htmlspecialchars($_SESSION['username']) ?>!</p>

    <h3>Последние посты</h3>
    <?php
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("
            SELECT p.*, u.username
            FROM posts p
            JOIN users u ON p.user_id = u.id
            WHERE p.visibility = 'public'
            ORDER BY p.created_at DESC
            LIMIT 5
        ");
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($posts): ?>
            <?php foreach ($posts as $post): ?>
                <div style="border: 1px solid #ccc; padding: 10px; margin: 10px 0;">
                    <h4><?= htmlspecialchars($post['title']) ?></h4>
                    <p><?= nl2br(htmlspecialchars(substr($post['content'], 0, 200))) ?>...</p>
                    <small>Автор: <?= htmlspecialchars($post['username']) ?> | <?= $post['created_at'] ?></small>
                    <br>
                    <a href="/posts/view.php?id=<?= $post['id'] ?>">Читать далее</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Пока нет публичных постов.</p>
        <?php endif;

    } catch (Exception $e) {
        echo "<p>Ошибка загрузки постов: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    ?>
<?php endif; ?>
