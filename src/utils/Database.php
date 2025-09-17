<?php
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $host = getenv('DB_HOST') ?: 'postgres';
        $dbname = getenv('DB_NAME') ?: 'blog_db';
        $user = getenv('DB_USER') ?: 'blog_user';
        $password = getenv('DB_PASS') ?: 'blog_password';
        $port = 5432;

        try {
            $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
            $this->connection = new PDO($dsn, $user, $password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Тестовый запрос для проверки соединения
            $this->connection->query("SELECT 1");

        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            // Не прерываем выполнение, чтобы показать красивую ошибку
            $this->connection = null;
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function isConnected() {
        return $this->connection !== null;
    }
}
