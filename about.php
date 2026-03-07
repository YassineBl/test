<?php
// minimal dynamic page; configuration not required unless you want to fetch data
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About OML PARA - Premium Parapharmacy</title>
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
                <li class="nav-item"><a href="about.php" class="nav-link active">About</a></li>
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

    <!-- About Hero -->
    <section class="hero">
        <div class="hero-content">
            <h1>About OML PARA</h1>
            <p>Dedicated to your health and wellness</p>
        </div>
    </section>

    <!-- About Content -->
    <section class="about-section">
        <div class="container">
            <div class="about-content">
                <div class="about-text">
                    <h2>Our Story</h2>
                    <p>OML PARA was founded with a simple mission: to provide high-quality parapharmacy products and expert health guidance to our community. With over 15 years of experience in the wellness industry, we've built a reputation for excellence, integrity, and customer care.</p>
                    <p>Our team of trained professionals is committed to helping you find the right products for your health needs. Whether you're looking for skincare solutions, nutritional supplements, or first-aid supplies, we've got you covered.</p>
                </div>
                <div class="about-image">
                    <div class="image-placeholder">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission & Values -->
    <section class="mission-values">
        <div class="container">
            <h2>Our Mission & Values</h2>
            <div class="values-grid">
                <div class="value-card">
                    <i class="fas fa-target"></i>
                    <h3>Our Mission</h3>
                    <p>To empower individuals with access to premium parapharmacy products and expert guidance for optimal health and wellness.</p>
                </div>
                <div class="value-card">
                    <i class="fas fa-star"></i>
                    <h3>Quality First</h3>
                    <p>We only stock products that meet the highest quality standards and have been rigorously tested.</p>
                </div>
                <div class="value-card">
                    <i class="fas fa-handshake"></i>
                    <h3>Customer Trust</h3>
                    <p>Building trust through transparency, honesty, and exceptional customer service is at the core of everything we do.</p>
                </div>
                <div class="value-card">
                    <i class="fas fa-leaf"></i>
                    <h3>Sustainability</h3>
                    <p>We're committed to sustainable practices and offering environmentally responsible product options.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Team -->
    <section class="team-section">
        <div class="container">
            <h2>Our Team</h2>
            <p class="section-subtitle">Meet the dedicated professionals behind OML PARA</p>
            <div class="team-grid">
                <div class="team-card">
                    <div class="team-avatar">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <h3>Dr. Sarah Johnson</h3>
                    <p>Founder & Chief Pharmacist</p>
                </div>
                <div class="team-card">
                    <div class="team-avatar">
                        <i class="fas fa-user-nurse"></i>
                    </div>
                    <h3>Emily Roberts</h3>
                    <p>Wellness Specialist</p>
                </div>
                <div class="team-card">
                    <div class="team-avatar">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <h3>Michael Chen</h3>
                    <p>Product Manager</p>
                </div>
                <div class="team-card">
                    <div class="team-avatar">
                        <i class="fas fa-user-headset"></i>
                    </div>
                    <h3>Lisa Anderson</h3>
                    <p>Customer Support Lead</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics -->
    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>15+</h3>
                    <p>Years of Experience</p>
                </div>
                <div class="stat-card">
                    <h3>50,000+</h3>
                    <p>Happy Customers</p>
                </div>
                <div class="stat-card">
                    <h3>500+</h3>
                    <p>Premium Products</p>
                </div>
                <div class="stat-card">
                    <h3>98%</h3>
                    <p>Customer Satisfaction</p>
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
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="contact.php">Contact</a></li>
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
    </footer>

    <script src="script.js"></script>
</body>
</html>