<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$order_id = $_GET['id'];

// Get order details
$stmt = $conn->prepare("SELECT o.*, u.name, u.email, u.phone 
                        FROM orders o 
                        JOIN users u ON o.user_id = u.id 
                        WHERE o.id = ? AND o.user_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: index.php");
    exit();
}

// Get order items
$stmt = $conn->prepare("SELECT oi.*, p.name as product_name 
                        FROM order_items oi 
                        JOIN products p ON oi.product_id = p.id 
                        WHERE oi.order_id = ?");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success - Food Delivery</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .navbar {
            background-color:rgb(126, 71, 255) !important;
        }
        .navbar-brand, .nav-link {
            color: white !important;
        }
        .success-icon {
            font-size: 5rem;
            color:rgb(243, 85, 23);
        }
        .order-details {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
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
                        <a class="nav-link" href="orders.php">My Orders</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Success Message -->
    <div class="container py-5">
        <div class="text-center mb-4">
            <i class="fas fa-check-circle success-icon mb-3"></i>
            <h2>Order Placed Successfully!</h2>
            <p class="lead">Thank you for your order. We'll start preparing your food right away!</p>
        </div>

        <!-- Order Details -->
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="order-details">
                    <h4 class="mb-3">Order Details</h4>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Order ID:</strong> #<?php echo $order['id']; ?></p>
                            <p><strong>Order Date:</strong> <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></p>
                            <p><strong>Payment Method:</strong> <?php echo ucfirst($order['payment_method']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Name:</strong> <?php echo $order['name']; ?></p>
                            <p><strong>Email:</strong> <?php echo $order['email']; ?></p>
                            <p><strong>Phone:</strong> <?php echo $order['phone']; ?></p>
                        </div>
                    </div>

                    <h5 class="mb-3">Delivery Address</h5>
                    <p class="mb-4"><?php echo nl2br($order['delivery_address']); ?></p>

                    <h5 class="mb-3">Order Items</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td><?php echo $item['product_name']; ?></td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td>₹<?php echo number_format($item['price'], 2); ?></td>
                                        <td>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                    <td><strong>₹<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="index.php" class="btn btn-primary me-2">
                        <i class="fas fa-utensils me-2"></i>Order More Food
                    </a>
                    <a href="orders.php" class="btn btn-outline-primary">
                        <i class="fas fa-list me-2"></i>View All Orders
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
