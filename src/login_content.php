<h2>Вход</h2>

<form method="post" action="/login.php">
    <div>
        <label>Имя пользователя:</label>
        <input type="text" name="username" required>
    </div>
    <div>
        <label>Пароль:</label>
        <input type="password" name="password" required>
    </div>
    <button type="submit">Войти</button>
</form>

<p>Нет аккаунта? <a href="/register.php">Зарегистрируйтесь здесь</a></p>
