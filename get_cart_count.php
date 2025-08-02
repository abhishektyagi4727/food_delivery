<?php
session_start();
header('Content-Type: text/plain');

if (!isset($_SESSION['user_id'])) {
    echo '0';
    exit();
}

if (!isset($_SESSION['cart'])) {
    echo '0';
    exit();
}

$total_items = 0;
foreach ($_SESSION['cart'] as $quantity) {
    $total_items += $quantity;
}

echo $total_items;
?>
