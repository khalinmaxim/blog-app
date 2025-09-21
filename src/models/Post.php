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

    public function delete($postId, $userId) {
        // Проверяем, принадлежит ли пост пользователю
        $stmt = $this->db->prepare("SELECT user_id FROM posts WHERE id = ?");
        $stmt->execute([$postId]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$post) {
            throw new Exception("Пост не найден");
        }

        if ($post['user_id'] != $userId) {
            throw new Exception("Вы можете удалять только свои посты");
        }

        $this->db->beginTransaction();

        try {
            // Удаляем связи с тегами
            $stmt = $this->db->prepare("DELETE FROM post_tags WHERE post_id = ?");
            $stmt->execute([$postId]);

            // Удаляем запросы доступа
            $stmt = $this->db->prepare("DELETE FROM post_requests WHERE post_id = ?");
            $stmt->execute([$postId]);

            // Удаляем комментарии
            $stmt = $this->db->prepare("DELETE FROM comments WHERE post_id = ?");
            $stmt->execute([$postId]);

            // Удаляем сам пост
            $stmt = $this->db->prepare("DELETE FROM posts WHERE id = ?");
            $result = $stmt->execute([$postId]);

            $this->db->commit();
            return $result;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Ошибка при удалении поста: " . $e->getMessage());
        }
    }

    public function getPostWithOwnerCheck($postId, $userId) {
        $stmt = $this->db->prepare("
            SELECT p.*, u.username
            FROM posts p
            JOIN users u ON p.user_id = u.id
            WHERE p.id = ? AND p.user_id = ?
        ");
        $stmt->execute([$postId, $userId]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($post) {
            // Добавляем информацию о тегах
            $post['tags'] = $this->getPostTags($postId);
        }

        return $post;
    }

    public function update($postId, $userId, $title, $content, $visibility, $tags = []) {
        // Проверяем, принадлежит ли пост пользователю
        $stmt = $this->db->prepare("SELECT user_id FROM posts WHERE id = ?");
        $stmt->execute([$postId]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$post) {
            throw new Exception("Пост не найден");
        }

        if ($post['user_id'] != $userId) {
            throw new Exception("Вы можете редактировать только свои посты");
        }

        // Валидация входных данных
        if (empty($title) || empty($content)) {
            throw new Exception("Заголовок и содержание не могут быть пустыми");
        }

        if (!in_array($visibility, ['public', 'private', 'request'])) {
            throw new Exception("Неверный тип видимости");
        }

        $this->db->beginTransaction();

        try {
            // Обновляем пост
            $stmt = $this->db->prepare(
                "UPDATE posts SET title = ?, content = ?, visibility = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?"
            );
            $stmt->execute([$title, $content, $visibility, $postId]);

            // Удаляем старые теги
            $stmt = $this->db->prepare("DELETE FROM post_tags WHERE post_id = ?");
            $stmt->execute([$postId]);

            // Добавляем новые теги
            if (!empty($tags) && is_array($tags)) {
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
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Ошибка при обновлении поста: " . $e->getMessage());
        }
    }

    public function getPostTags($postId) {
        $stmt = $this->db->prepare("
            SELECT t.name
            FROM tags t
            JOIN post_tags pt ON t.id = pt.tag_id
            WHERE pt.post_id = ?
        ");
        $stmt->execute([$postId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getPostWithTags($postId) {
        $stmt = $this->db->prepare("
            SELECT p.*, u.username,
                   COALESCE(array_agg(t.name) FILTER (WHERE t.name IS NOT NULL), '{}') as tags
            FROM posts p
            JOIN users u ON p.user_id = u.id
            LEFT JOIN post_tags pt ON p.id = pt.post_id
            LEFT JOIN tags t ON pt.tag_id = t.id
            WHERE p.id = ?
            GROUP BY p.id, u.username
        ");
        $stmt->execute([$postId]);
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        // Преобразуем строку тегов в массив, если это необходимо
        if ($post && isset($post['tags']) && is_string($post['tags'])) {
            // Убираем фигурные скобки и разбиваем по запятым
            $tagsString = trim($post['tags'], '{}');
            $post['tags'] = $tagsString ? array_map('trim', explode(',', $tagsString)) : [];
        }

        return $post;
    }

   public function getFeed($userId) {
       try {
           $stmt = $this->db->prepare("
               SELECT DISTINCT p.*, u.username
               FROM posts p
               JOIN users u ON p.user_id = u.id
               WHERE p.user_id IN (
                   SELECT target_id FROM subscriptions WHERE subscriber_id = ?
               )
               AND p.visibility IN ('public', 'request') -- Только публичные и по запросу
               ORDER BY p.created_at DESC
               LIMIT 20
           ");
           $stmt->execute([$userId]);
           return $stmt->fetchAll(PDO::FETCH_ASSOC);
       } catch (PDOException $e) {
           error_log("Feed error: " . $e->getMessage());
           return [];
       }
   }

   public function getUserFeedWithOwnPosts($userId) {
       try {
           $stmt = $this->db->prepare("
               SELECT DISTINCT p.*, u.username
               FROM posts p
               JOIN users u ON p.user_id = u.id
               WHERE (
                   p.visibility = 'public'
                   OR p.user_id IN (
                       SELECT target_id FROM subscriptions WHERE subscriber_id = ?
                   )
                   OR p.user_id = ? -- Включаем собственные посты
               )
               ORDER BY p.created_at DESC
               LIMIT 20
           ");
           $stmt->execute([$userId, $userId]);
           return $stmt->fetchAll(PDO::FETCH_ASSOC);
       } catch (PDOException $e) {
           error_log("User feed error: " . $e->getMessage());
           return [];
       }
   }

   public function getPublicPosts($limit = 10) {
       try {
           $stmt = $this->db->prepare("
               SELECT p.*, u.username
               FROM posts p
               JOIN users u ON p.user_id = u.id
               WHERE p.visibility = 'public'
               ORDER BY p.created_at DESC
               LIMIT ?
           ");
           $stmt->execute([$limit]);
           return $stmt->fetchAll(PDO::FETCH_ASSOC);
       } catch (PDOException $e) {
           error_log("Public posts error: " . $e->getMessage());
           return [];
       }
   }

   public function getRecommendedPosts($userId, $limit = 10) {
       try {
           $stmt = $this->db->prepare("
               SELECT DISTINCT p.*, u.username
               FROM posts p
               JOIN users u ON p.user_id = u.id
               WHERE p.visibility = 'public'
               AND p.user_id != ?
               AND p.user_id NOT IN (
                   SELECT target_id FROM subscriptions WHERE subscriber_id = ?
               )
               ORDER BY p.created_at DESC
               LIMIT ?
           ");
           $stmt->execute([$userId, $userId, $limit]);
           return $stmt->fetchAll(PDO::FETCH_ASSOC);
       } catch (PDOException $e) {
           error_log("Recommended posts error: " . $e->getMessage());
           return [];
       }
   }
}
