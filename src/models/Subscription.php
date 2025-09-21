<?php
class Subscription {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Подписаться на пользователя
    public function subscribe($subscriberId, $targetId) {
        if ($subscriberId == $targetId) {
            throw new Exception("Нельзя подписаться на самого себя");
        }

        // Проверяем существование целевого пользователя
        $userStmt = $this->db->prepare("SELECT id FROM users WHERE id = ?");
        $userStmt->execute([$targetId]);
        if (!$userStmt->fetch()) {
            throw new Exception("Пользователь не найден");
        }

        // Проверяем, не подписаны ли уже
        $checkStmt = $this->db->prepare("SELECT * FROM subscriptions WHERE subscriber_id = ? AND target_id = ?");
        $checkStmt->execute([$subscriberId, $targetId]);
        if ($checkStmt->fetch()) {
            throw new Exception("Вы уже подписаны на этого пользователя");
        }

        $stmt = $this->db->prepare("INSERT INTO subscriptions (subscriber_id, target_id) VALUES (?, ?)");
        return $stmt->execute([$subscriberId, $targetId]);
    }

    // Отписаться от пользователя
    public function unsubscribe($subscriberId, $targetId) {
        $stmt = $this->db->prepare("DELETE FROM subscriptions WHERE subscriber_id = ? AND target_id = ?");
        return $stmt->execute([$subscriberId, $targetId]);
    }

    // Проверить, подписан ли пользователь
    public function isSubscribed($subscriberId, $targetId) {
        $stmt = $this->db->prepare("SELECT * FROM subscriptions WHERE subscriber_id = ? AND target_id = ?");
        $stmt->execute([$subscriberId, $targetId]);
        return (bool) $stmt->fetch();
    }

    // Получить подписки пользователя
    public function getSubscriptions($userId) {
        $stmt = $this->db->prepare("
            SELECT u.id, u.username, s.created_at
            FROM subscriptions s
            JOIN users u ON s.target_id = u.id
            WHERE s.subscriber_id = ?
            ORDER BY s.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Получить подписчиков пользователя
    public function getSubscribers($userId) {
        $stmt = $this->db->prepare("
            SELECT u.id, u.username, s.created_at
            FROM subscriptions s
            JOIN users u ON s.subscriber_id = u.id
            WHERE s.target_id = ?
            ORDER BY s.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Получить количество подписчиков
    public function getSubscribersCount($userId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM subscriptions WHERE target_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }

    // Получить количество подписок
    public function getSubscriptionsCount($userId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM subscriptions WHERE subscriber_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }
}
