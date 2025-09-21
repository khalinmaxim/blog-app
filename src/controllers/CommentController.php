<?php
require_once __DIR__ . '/../models/Comment.php';

class CommentController {
    private $commentModel;

    public function __construct() {
        $this->commentModel = new Comment();
    }

    // Добавить комментарий
    public function add() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Для комментирования необходимо войти в систему";
            header('Location: /login.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $_SESSION['error'] = "Неверный метод запроса";
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
            exit;
        }

        $postId = $_POST['post_id'] ?? null;
        $content = $_POST['content'] ?? '';

        if (!$postId) {
            $_SESSION['error'] = "ID поста не указан";
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
            exit;
        }

        try {
            $commentId = $this->commentModel->create($postId, $_SESSION['user_id'], $content);
            $_SESSION['success'] = "Комментарий добавлен!";
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }

        header('Location: /posts/view.php?id=' . $postId);
        exit;
    }

    // Удалить комментарий
    public function delete() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['error'] = "Необходима авторизация";
            header('Location: /login.php');
            exit;
        }

        $commentId = $_GET['id'] ?? null;
        if (!$commentId) {
            $_SESSION['error'] = "ID комментария не указан";
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
            exit;
        }

        try {
            // Получаем комментарий чтобы узнать post_id для редиректа
            $comment = $this->commentModel->getById($commentId);
            if (!$comment) {
                throw new Exception("Комментарий не найден");
            }

            $this->commentModel->delete($commentId, $_SESSION['user_id']);
            $_SESSION['success'] = "Комментарий удален!";

            header('Location: /posts/view.php?id=' . $comment['post_id']);
            exit;

        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/'));
            exit;
        }
    }
}
