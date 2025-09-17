<?php
class Post {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($userId, $title, $content, $visibility, $tags = []) {
        $this->db->beginTransaction();

        try {
            // Создаем пост
            $stmt = $this->db->prepare(
                "INSERT INTO posts (user_id, title, content, visibility) VALUES (?, ?, ?, ?) RETURNING id"
            );
            $stmt->execute([$userId, $title, $content, $visibility]);
            $postId = $stmt->fetch(PDO::FETCH_ASSOC)['id'];

            // Добавляем теги
            foreach ($tags as $tagName) {
                $tagName = trim($tagName);
                if (!empty($tagName)) {
                    $tagId = $this->getOrCreateTag($tagName);
                    $stmt = $this->db->prepare(
                        "INSERT INTO post_tags (post_id, tag_id) VALUES (?, ?)"
                    );
                    $stmt->execute([$postId, $tagId]);
                }
            }

            $this->db->commit();
            return $postId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function getOrCreateTag($tagName) {
        $tagName = strtolower(trim($tagName));

        // Проверяем существование тега
        $stmt = $this->db->prepare("SELECT id FROM tags WHERE name = ?");
        $stmt->execute([$tagName]);
        $tag = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($tag) {
            return $tag['id'];
        }

        // Создаем новый тег
        $stmt = $this->db->prepare("INSERT INTO tags (name) VALUES (?) RETURNING id");
        $stmt->execute([$tagName]);
        return $stmt->fetch(PDO::FETCH_ASSOC)['id'];
    }

    public function getPostsByUser($userId) {
        $stmt = $this->db->prepare("
            SELECT p.*, u.username,
                   array_agg(t.name) as tags
            FROM posts p
            JOIN users u ON p.user_id = u.id
            LEFT JOIN post_tags pt ON p.id = pt.post_id
            LEFT JOIN tags t ON pt.tag_id = t.id
            WHERE p.user_id = ?
            GROUP BY p.id, u.username
            ORDER BY p.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPublicPosts() {
        $stmt = $this->db->query("
            SELECT p.*, u.username,
                   array_agg(t.name) as tags
            FROM posts p
            JOIN users u ON p.user_id = u.id
            LEFT JOIN post_tags pt ON p.id = pt.post_id
            LEFT JOIN tags t ON pt.tag_id = t.id
            WHERE p.visibility = 'public'
            GROUP BY p.id, u.username
            ORDER BY p.created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById($postId) {
        $stmt = $this->db->prepare("
            SELECT p.*, u.username
            FROM posts p
            JOIN users u ON p.user_id = u.id
            WHERE p.id = ?
        ");
        $stmt->execute([$postId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
