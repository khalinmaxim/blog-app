<?php
session_start();
require_once __DIR__ . '/../controllers/CommentController.php';

$controller = new CommentController();
$controller->add();
