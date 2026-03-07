<?php
require_once 'config.php';

// load categories for display
$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - OML PARA Services</title>
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
                <li class="nav-item"><a href="index.php" class="nav-link">Home</a></li>
                <li class="nav-item"><a href="about.php" class="nav-link">About</a></li>
                <li class="nav-item"><a href="menu.php" class="nav-link active">Menu</a></li>
                <li class="nav-item"><a href="products.php" class="nav-link">Products</a></li>
                <li class="nav-item"><a href="contact.php" class="nav-link">Contact</a></li>
            </ul>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Menu Hero -->
    <section class="hero">
        <div class="hero-content">
            <h1>Our Services & Categories</h1>
            <p>Explore all the products and services we offer</p>
        </div>
    </section>

    <!-- Services Menu -->
    <section class="menu-section">
        <div class="container">
            <h2>Product Categories</h2>
            <div class="menu-grid">
                <?php if (count($categories) === 0): ?>
                    <p>No categories have been added yet.</p>
                <?php else: ?>
                    <?php foreach ($categories as $cat): ?>
                        <div class="menu-card">
                            <div class="menu-icon">
                                <!-- simple icon based on slug -->
                                <i class="fas fa-tag"></i>
                            </div>
                            <h3><?php echo sanitize($cat['name']); ?></h3>
                            <p><?php echo sanitize($cat['description']); ?></p>
                            <button class="btn-explore-menu"><a href="products.php?category=<?php echo urlencode($cat['id']); ?>">View items</a></button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Professional Services -->
    <section class="professional-services">
        <div class="container">
            <h2>Our Professional Services</h2>
            <div class="services-grid">
                <div class="service-item">
                    <i class="fas fa-user-md"></i>
                    <h3>Free Consultation</h3>
                    <p>Get personalized advice from our licensed pharmacists</p>
                </div>
                <div class="service-item">
                    <i class="fas fa-truck"></i>
                    <h3>Fast Delivery</h3>
                    <p>Same-day delivery available for orders within the city</p>
                </div>
                <div class="service-item">
                    <i class="fas fa-phone"></i>
                    <h3>Customer Support</h3>
                    <p>24/7 support team ready to assist you</p>
                </div>
                <div class="service-item">
                    <i class="fas fa-prescription-bottle"></i>
                    <h3>Prescription Management</h3>
                    <p>Professional handling of prescription medications</p>
                </div>
                <div class="service-item">
                    <i class="fas fa-award"></i>
                    <h3>Loyalty Program</h3>
                    <p>Earn rewards on every purchase</p>
                </div>
                <div class="service-item">
                    <i class="fas fa-shield-alt"></i>
                    <h3>Quality Guarantee</h3>
                    <p>100% authentic products with satisfaction guarantee</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>OML PARA</h3>
                <p>Your partner in health and wellness</p>
            </div>
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h4>Follow Us</h4>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 OML PARA. All rights reserved.</p>
        </div>
    </footer>

    <script src="script.js"></script>
    <script>
        document.querySelectorAll('.btn-explore-menu').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const categoryName = this.parentElement.querySelector('h3').textContent;
                alert(`Exploring ${categoryName}...`);
            });
        });
    </script>
</body>
</html>