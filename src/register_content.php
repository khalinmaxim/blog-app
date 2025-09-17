<h2>Регистрация</h2>

<form method="post" action="/register.php">
    <div>
        <label>Имя пользователя:</label>
        <input type="text" name="username" required>
    </div>
    <div>
        <label>Email:</label>
        <input type="email" name="email" required>
    </div>
    <div>
        <label>Пароль:</label>
        <input type="password" name="password" required>
    </div>
    <div>
        <label>Подтвердите пароль:</label>
        <input type="password" name="confirm_password" required>
    </div>
    <button type="submit">Зарегистрироваться</button>
</form>

<p>Уже есть аккаунт? <a href="/login.php">Войдите здесь</a></p>
