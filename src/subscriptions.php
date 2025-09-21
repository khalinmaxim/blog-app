<?php
session_start();
require_once __DIR__ . '/controllers/SubscriptionController.php';
require_once __DIR__ . '/models/User.php';

$controller = new SubscriptionController();

// HTML обертка
$content = '';
include __DIR__ . '/views/layout.php';
