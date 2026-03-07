<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact OML PARA</title>
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
                <li class="nav-item"><a href="menu.php" class="nav-link">Menu</a></li>
                <li class="nav-item"><a href="products.php" class="nav-link">Products</a></li>
                <li class="nav-item"><a href="contact.php" class="nav-link active">Contact</a></li>
            </ul>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Contact Hero -->
    <section class="hero">
        <div class="hero-content">
            <h1>Contact OML PARA</h1>
            <p>We're here to help you with any questions</p>
        </div>
    </section>

    <!-- Contact Content -->
    <section class="contact-section">
        <div class="container">
            <div class="contact-content">
                <!-- Contact Form -->
                <div class="contact-form-container">
                    <h2>Send us a Message</h2>
                    <form class="contact-form" id="contactForm">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone">
                        </div>

                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" required>
                        </div>

                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" rows="5" required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Send Message</button>
                    </form>
                </div>

                <!-- Contact Info -->
                <div class="contact-info-container">
                    <h2>Contact Information</h2>
                    <div class="contact-info">
                        <div class="info-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <h3>Location</h3>
                                <p>123 Health Street<br>Medical District<br>City, State 12345</p>
                            </div>
                        </div>

                        <div class="info-item">
                            <i class="fas fa-phone"></i>
                            <div>
                                <h3>Phone</h3>
                                <p>+1 (555) 123-4567<br>+1 (555) 123-4568</p>
                            </div>
                        </div>

                        <div class="info-item">
                            <i class="fas fa-envelope"></i>
                            <div>
                                <h3>Email</h3>
                                <p>info@omlpara.com<br>support@omlpara.com</p>
                            </div>
                        </div>

                        <div class="info-item">
                            <i class="fas fa-clock"></i>
                            <div>
                                <h3>Working Hours</h3>
                                <p>Mon - Fri: 9:00 AM - 6:00 PM<br>Sat: 10:00 AM - 4:00 PM<br>Sun: Closed</p>
                            </div>
                        </div>
                    </div>

                    <!-- Social Links -->
                    <div class="contact-social">
                        <h3>Follow Us</h3>
                        <div class="social-links-contact">
                            <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="map-section">
        <div class="container">
            <h2>Find Us Here</h2>
            <div class="map-placeholder">
                <i class="fas fa-map"></i>
                <p>Interactive map will be displayed here</p>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="container">
            <h2>Frequently Asked Questions</h2>
            <div class="faq-grid">
                <div class="faq-item">
                    <h3>What are your delivery options?</h3>
                    <p>We offer same-day delivery within the city, standard delivery within 2-3 business days, and international shipping options.</p>
                </div>
                <div class="faq-item">
                    <h3>Do you accept returns?</h3>
                    <p>Yes, we accept returns within 30 days of purchase for unopened, original products with receipt.</p>
                </div>
                <div class="faq-item">
                    <h3>Are your products certified?</h3>
                    <p>All our products are certified by relevant health authorities and tested for quality and safety.</p>
                </div>
                <div class="faq-item">
                    <h3>Do you offer professional consultations?</h3>
                    <p>Yes, our licensed pharmacists offer free consultations in-store or by phone. Just contact us to schedule an appointment.</p>
                </div>
                <div class="faq-item">
                    <h3>What payment methods do you accept?</h3>
                    <p>We accept credit cards, debit cards, digital wallets, and bank transfers. All transactions are secure and encrypted.</p>
                </div>
                <div class="faq-item">
                    <h3>How can I track my order?</h3>
                    <p>You'll receive a tracking number via email. You can use it to monitor your order status in real-time on our website.</p>
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
</body>
</html>