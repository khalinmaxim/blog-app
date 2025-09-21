<?php
session_start();
require_once __DIR__ . '/controllers/SubscriptionController.php';

$controller = new SubscriptionController();
$controller->subscribe();
