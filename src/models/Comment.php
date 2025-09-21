<?php
require_once __DIR__ . '/../utils/Database.php';

class Comment {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Добавить комментарий
    public function create($postId, $userId, $content) {
        if (empty(trim($content))) {
            throw new Exception("Комментарий не может быть пустым");
        }

        $stmt = $this->db->prepare(
            "INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?) RETURNING id"
        );
        $stmt->execute([$postId, $userId, trim($content)]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['id'];
    }

    // Получить комментарии для поста
    public function getByPostId($postId) {
        $stmt = $this->db->prepare("
            SELECT c.*, u.username
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.post_id = ?
            ORDER BY c.created_at ASC
        ");
        $stmt->execute([$postId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Получить количество комментариев для поста
    public function getCountByPostId($postId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM comments WHERE post_id = ?");
        $stmt->execute([$postId]);
        return $stmt->fetchColumn();
    }

    // Удалить комментарий
    public function delete($commentId, $userId) {
        // Проверяем, принадлежит ли комментарий пользователю
        $stmt = $this->db->prepare("SELECT user_id FROM comments WHERE id = ?");
        $stmt->execute([$commentId]);
        $comment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$comment) {
            throw new Exception("Комментарий не найден");
        }

        if ($comment['user_id'] != $userId) {
            throw new Exception("Вы можете удалять только свои комментарии");
        }

        $stmt = $this->db->prepare("DELETE FROM comments WHERE id = ?");
        return $stmt->execute([$commentId]);
    }

    // Получить комментарий по ID
    public function getById($commentId) {
        $stmt = $this->db->prepare("
            SELECT c.*, u.username
            FROM comments c
            JOIN users u ON c.user_id = u.id
            WHERE c.id = ?
        ");
        $stmt->execute([$commentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
