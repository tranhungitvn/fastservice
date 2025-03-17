<?php
require_once 'config/config.php';
require_once 'includes/Database.php';
require_once 'includes/User.php';
require_once 'includes/Order.php';

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user = new User();
$order = new Order();

$user_info = $user->getCurrentUser();
$user_orders = $order->getUserOrders($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport
