<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if cart is empty
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

// Get user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Get cart items
$cart_items = [];
$total = 0;
if (!empty($_SESSION['cart'])) {
    $product_ids = array_keys($_SESSION['cart']);
    $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
    $stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($placeholders)");
    $stmt->execute($product_ids);
    $products = $stmt->fetchAll();

    foreach ($products as $product) {
        $quantity = $_SESSION['cart'][$product['id']];
        $cart_items[] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'quantity' => $quantity,
            'subtotal' => $product['price'] * $quantity
        ];
        $total += $product['price'] * $quantity;
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $delivery_address = $_POST['delivery_address'];
    $payment_method = $_POST['payment_method'];
    
    try {
        $conn->beginTransaction();

        // Create order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, delivery_address, payment_method) 
                               VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $total, $delivery_address, $payment_method]);
        $order_id = $conn->lastInsertId();

        // Create order items
        $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) 
                               VALUES (?, ?, ?, ?)");
        foreach ($cart_items as $item) {
            $stmt->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
        }

        $conn->commit();
        
        // Clear cart
        $_SESSION['cart'] = [];
        
        // Redirect to success page
        header("Location: order_success.php?id=" . $order_id);
        exit();
    } catch (Exception $e) {
        $conn->rollBack();
        $error = "Failed to place order. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Food Delivery</title>
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
            background-color:rgb(126, 71, 255);
            border-color:rgb(135, 71, 255);
        }
        .btn-primary:hover {
            background-color:rgb(134, 107, 255);
            border-color:rgb(151, 107, 255);
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
        </div>
    </nav>

    <!-- Checkout Section -->
    <div class="container py-5">
        <div class="row">
            <!-- Order Summary -->
            <div class="col-md-4 order-md-2 mb-4">
                <h4 class="d-flex justify-content-between align-items-center mb-3">
                    <span>Order Summary</span>
                    <span class="badge bg-primary rounded-pill"><?php echo count($cart_items); ?></span>
                </h4>
                <ul class="list-group mb-3">
                    <?php foreach ($cart_items as $item): ?>
                        <li class="list-group-item d-flex justify-content-between lh-sm">
                            <div>
                                <h6 class="my-0"><?php echo $item['name']; ?></h6>
                                <small class="text-muted">Quantity: <?php echo $item['quantity']; ?></small>
                            </div>
                            <span class="text-muted">₹<?php echo number_format($item['subtotal'], 2); ?></span>
                        </li>
                    <?php endforeach; ?>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Total</span>
                        <strong>₹<?php echo number_format($total, 2); ?></strong>
                    </li>
                </ul>
            </div>

            <!-- Checkout Form -->
            <div class="col-md-8 order-md-1">
                <h4 class="mb-3">Checkout Details</h4>
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST" class="needs-validation" novalidate>
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" value="<?php echo $user['name']; ?>" readonly>
                        </div>

                        <div class="col-12">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" value="<?php echo $user['email']; ?>" readonly>
                        </div>

                        <div class="col-12">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone" value="<?php echo $user['phone']; ?>" readonly>
                        </div>

                        <div class="col-12">
                            <label for="delivery_address" class="form-label">Delivery Address</label>
                            <textarea class="form-control" id="delivery_address" name="delivery_address" rows="3" required><?php echo $user['address']; ?></textarea>
                            <div class="invalid-feedback">
                                Please enter your delivery address.
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select class="form-select" id="payment_method" name="payment_method" required>
                                <option value="">Choose...</option>
                                <option value="cash">Cash on Delivery</option>
                                <option value="card">Credit/Debit Card</option>
                            </select>
                            <div class="invalid-feedback">
                                Please select a payment method.
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <button class="w-100 btn btn-primary btn-lg" type="submit">Place Order</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
</body>
</html>
