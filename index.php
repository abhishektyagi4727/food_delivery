<?php
session_start();
require_once 'config/database.php';

// Get all categories
$stmt = $conn->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll();

// Get products with optional category filter
$category_id = isset($_GET['category']) ? $_GET['category'] : null;
$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.status = 'available'";
if ($category_id) {
    $query .= " AND p.category_id = " . intval($category_id);
}
$query .= " ORDER BY p.created_at DESC";
$stmt = $conn->query($query);
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>zomato-home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                        url('https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            margin-bottom: 40px;
        }
        .category-filter {
            background-color: #f8f9fa;
            padding: 20px 0;
            margin-bottom: 40px;
        }
        .product-card {
            transition: transform 0.3s;
            margin-bottom: 30px;
        }
        .product-card:hover {
            transform: translateY(-5px);
        }
        .product-image {
            height: 200px;
            object-fit: cover;
        }
        .navbar {
            background-color:rgb(151, 71, 255) !important;
        }
        .navbar-brand, .nav-link {
            color: white !important;
        }
        .btn-primary {
            background-color:rgb(120, 71, 255);
            border-color:rgb(111, 71, 255);
        }
        .btn-primary:hover {
            background-color:rgb(139, 107, 255);
            border-color:rgb(122, 107, 255);
        }
        .category-btn {
            margin: 5px;
        }
        .category-btn.active {
            background-color: #ff6b81;
            border-color: #ff6b81;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-utensils me-2"></i>Bytes Express
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="cart.php">
                                <i class="fas fa-shopping-cart"></i> Cart
                                <span class="badge bg-light text-dark" id="cart-count">0</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="orders.php">My Orders</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">Profile</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-center">
        <div class="container">
            <h1 class="display-4">Delicious Food Delivered</h1>
            <p class="lead">Order your favorite meals from the best restaurants in town</p>
        </div>
    </section>

    <!-- Category Filter -->
    <section class="category-filter">
        <div class="container">
            <div class="text-center">
                <a href="index.php" class="btn btn-primary category-btn <?php echo !$category_id ? 'active' : ''; ?>">
                    All Categories
                </a>
                <?php foreach ($categories as $category): ?>
                    <a href="index.php?category=<?php echo $category['id']; ?>" 
                       class="btn btn-primary category-btn <?php echo $category_id == $category['id'] ? 'active' : ''; ?>">
                        <?php echo $category['name']; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section class="products-section py-5">
        <div class="container">
            <div class="row">
                <?php foreach ($products as $product): ?>
                    <div class="col-md-4">
                        <div class="card product-card">
                            <img src="<?php echo $product['image']; ?>" class="card-img-top product-image" 
                                 alt="<?php echo $product['name']; ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $product['name']; ?></h5>
                                <p class="card-text text-muted"><?php echo $product['category_name']; ?></p>
                                <p class="card-text"><?php echo substr($product['description'], 0, 100) . '...'; ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">₹<?php echo number_format($product['price'], 2); ?></h6>
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <button class="btn btn-primary add-to-cart" data-id="<?php echo $product['id']; ?>">
                                            Add to Cart
                                        </button>
                                    <?php else: ?>
                                        <a href="login.php" class="btn btn-primary">Login to Order</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>About Us</h5>
                    <p>We deliver the best food in town right to your doorstep. Quality food, quick delivery, and excellent service!</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white">Terms & Conditions</a></li>
                        <li><a href="#" class="text-white">Privacy Policy</a></li>
                        <li><a href="#" class="text-white">Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-phone me-2"></i> +919625958050</li>
                        <li><i class="fas fa-envelope me-2"></i> abhishektyagi4727@gmail.com</li>
                        <li><i class="fas fa-map-marker-alt me-2"></i> MUZAFFARNAGAR</li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Add to cart functionality
            $('.add-to-cart').click(function() {
                var productId = $(this).data('id');
                $.ajax({
                    url: 'add_to_cart.php',
                    method: 'POST',
                    data: { product_id: productId },
                    success: function(response) {
                        alert('Product added to cart!');
                        updateCartCount();
                    },
                    error: function() {
                        alert('Error adding product to cart!');
                    }
                });
            });

            // Update cart count
            function updateCartCount() {
                $.ajax({
                    url: 'get_cart_count.php',
                    method: 'GET',
                    success: function(response) {
                        $('#cart-count').text(response);
                    }
                });
            }

            // Initial cart count update
            updateCartCount();
        });
    </script>
</body>
</html>
