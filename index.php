<?php
require_once 'config.php';

// Get featured products from database
$stmt = $pdo->query('SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_disabled = 0 AND p.is_available = 1 LIMIT 6');
$featured_products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OML PARA - Premium Parapharmacy</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <i class="fas fa-capsules"></i>
                <span>OML PARA</span>
            </div>
            <ul class="nav-menu">
                <li class="nav-item"><a href="index.php" class="nav-link active">Home</a></li>
                <li class="nav-item"><a href="about.php" class="nav-link">About</a></li>
                <li class="nav-item"><a href="menu.php" class="nav-link">Menu</a></li>
                <li class="nav-item"><a href="products.php" class="nav-link">Products</a></li>
                <li class="nav-item"><a href="contact.php" class="nav-link">Contact</a></li>
                <li class="nav-item"><a href="admin/login.php" class="nav-link">Admin</a></li>
                
            </ul>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Welcome to OML PARA</h1>
            <p>Your trusted source for premium parapharmacy products and wellness solutions</p>
            <div class="hero-buttons">
                <button class="btn btn-primary"><a href="products.php">Shop Now</a></button>
                <button class="btn btn-secondary"><a href="about.html">Learn More</a></button>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section class="featured-products">
        <div class="container">
            <h2>Featured Products</h2>
            <div class="products-grid">
                <?php foreach ($featured_products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <img src="<?php echo $product['image_url'] ?: 'https://via.placeholder.com/300x300?text=' . urlencode($product['name']); ?>" alt="<?php echo sanitize($product['name']); ?>">
                        </div>
                        <div class="product-info">
                            <h3><?php echo sanitize($product['name']); ?></h3>
                            <p class="category">
                                <i class="fas fa-tag"></i> <?php echo sanitize($product['category_name']); ?>
                            </p>
                            <p class="price">
                                <span class="price-amount">$<?php echo number_format($product['price'], 2); ?></span>
                            </p>
                            <button class="btn btn-small"><a href="products.php">View Details</a></button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2>Quality Products You Can Trust</h2>
            <p>We provide premium parapharmacy products sourced from trusted suppliers worldwide</p>
            <a href="products.php" class="btn btn-primary">Explore Our Collection</a>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <div class="feature-grid">
                <div class="feature-item">
                    <i class="fas fa-check-circle"></i>
                    <h3>Quality Assured</h3>
                    <p>All products are verified and quality checked</p>
                </div>
                <div class="feature-item">
                    <i class="fas fa-shipping-fast"></i>
                    <h3>Fast Delivery</h3>
                    <p>Quick and reliable delivery to your doorstep</p>
                </div>
                <div class="feature-item">
                    <i class="fas fa-lock"></i>
                    <h3>Secure Ordering</h3>
                    <p>Your information is safe with our secure platform</p>
                </div>
                <div class="feature-item">
                    <i class="fas fa-headset"></i>
                    <h3>Customer Support</h3>
                    <p>24/7 support via WhatsApp and email</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>About Us</h3>
                    <p>OML PARA is your trusted source for premium parapharmacy products and wellness solutions.</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="products.php">Products</a></li>
                        <li><a href="about.html">About</a></li>
                        <li><a href="contact.html">Contact</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contact</h3>
                    <p>Email: info@omlpara.com</p>
                    <p>WhatsApp: +1234567890</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 OML PARA. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>
