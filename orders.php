<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user's orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - Food Delivery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .navbar {
            background-color:rgb(120, 71, 255) !important;
        }
        .navbar-brand, .nav-link {
            color: white !important;
        }
        .btn-primary {
            background-color:rgb(141, 71, 255);
            border-color:rgb(151, 71, 255);
        }
        .btn-primary:hover {
            background-color:rgb(151, 107, 255);
            border-color:rgb(147, 107, 255);
        }
        .order-card {
            transition: transform 0.3s;
        }
        .order-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-utensils me-2"></i>Food Delivery
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">
                            <i class="fas fa-shopping-cart"></i> Cart
                            <span class="badge bg-light text-dark"><?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="orders.php">My Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Orders Section -->
    <div class="container py-5">
        <h2 class="mb-4">My Orders</h2>

        <?php if (empty($orders)): ?>
            <div class="alert alert-info">
                You haven't placed any orders yet. <a href="index.php">Start ordering now</a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($orders as $order): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card order-card">
                            <div class="card-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Order #<?php echo $order['id']; ?></h5>
                                    <span class="badge bg-<?php 
                                        echo $order['status'] == 'pending' ? 'warning' : 
                                            ($order['status'] == 'processing' ? 'info' : 
                                            ($order['status'] == 'delivered' ? 'success' : 'danger')); 
                                    ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <small class="text-muted">Ordered on</small>
                                    <p class="mb-0"><?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></p>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted">Total Amount</small>
                                    <p class="mb-0">₹<?php echo number_format($order['total_amount'], 2); ?></p>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted">Payment Method</small>
                                    <p class="mb-0"><?php echo ucfirst($order['payment_method']); ?></p>
                                </div>
                                <div>
                                    <small class="text-muted">Delivery Address</small>
                                    <p class="mb-0"><?php echo nl2br($order['delivery_address']); ?></p>
                                </div>

                                <?php
                                // Get order items
                                $stmt = $conn->prepare("SELECT oi.*, p.name as product_name 
                                                      FROM order_items oi 
                                                      JOIN products p ON oi.product_id = p.id 
                                                      WHERE oi.order_id = ?");
                                $stmt->execute([$order['id']]);
                                $items = $stmt->fetchAll();
                                ?>

                                <div class="mt-3">
                                    <h6>Order Items</h6>
                                    <ul class="list-unstyled">
                                        <?php foreach ($items as $item): ?>
                                            <li>
                                                <?php echo $item['product_name']; ?> 
                                                <span class="text-muted">
                                                    (<?php echo $item['quantity']; ?> ₹<?php echo number_format($item['price'], 2); ?>)
                                                </span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                            <?php if ($order['status'] == 'delivered'): ?>
                                <div class="card-footer text-center">
                                    <a href="#" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-star me-1"></i>Rate Order
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
