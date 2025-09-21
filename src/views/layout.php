<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Блог приложение</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .header { background: #667eea; color: white; padding: 20px; margin-bottom: 20px; border-radius: 10px; }
        .nav { margin: 10px 0; }
        .nav a { margin-right: 15px; text-decoration: none; color: white; padding: 8px 16px; border-radius: 5px; background: rgba(255,255,255,0.2); }
        .nav a:hover { background: rgba(255,255,255,0.3); }
        .content { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
        form { margin: 20px 0; }
        input, textarea { width: 100%; padding: 10px; margin: 5px 0; border: 1px solid #ddd; border-radius: 5px; }
        button { background: #667eea; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #5a67d8; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Мой Блог</h1>
        <div class="nav">
            <a href="/">Главная</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/posts/create.php">Новый пост</a>
                <a href="/logout.php">Выйти (<?= htmlspecialchars($_SESSION['username']) ?>)</a>
            <?php else: ?>
                <a href="/login.php">Вход</a>
                <a href="/register.php">Регистрация</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="content">
        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <div class="success"><?= $success ?></div>
        <?php endif; ?>

        <?php echo $content; ?>
    </div>

    <script>
    // Функция подтверждения удаления с показом названия поста
    function confirmDelete(event, postTitle) {
        if (!confirm('Вы уверены, что хотите удалить пост?\n\n"' + postTitle + '"')) {
            event.preventDefault();
            return false;
        }
        return true;
    }
    </script>
</body>
</html>
