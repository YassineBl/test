<?php
require_once 'config.php';

// Get all categories
$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll();

// Determine if a category filter is applied
$categoryFilter = null;
if (isset($_GET['category']) && is_numeric($_GET['category'])) {
    $categoryFilter = (int) $_GET['category'];
}

// Build product query with optional category filter
$sql = 'SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_disabled = 0 AND p.is_available = 1';
$params = [];
if ($categoryFilter) {
    $sql .= ' AND p.category_id = ?';
    $params[] = $categoryFilter;
}
$sql .= ' ORDER BY p.created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - OML PARA</title>
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
                <li class="nav-item"><a href="products.php" class="nav-link active">Products</a></li>
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

    <!-- Products Hero -->
    <section class="hero">
        <div class="hero-content">
            <h1>Our Products</h1>
            <p>Premium quality parapharmacy products for all your wellness needs</p>
        </div>
    </section>

    <!-- Products Filter & Grid -->
    <section class="products-section">
        <div class="container">
            <div class="filter-buttons">
                <button class="filter-btn active" data-filter="all">All Products</button>
                <?php foreach ($categories as $cat): ?>
                    <button class="filter-btn" data-filter="<?php echo sanitize($cat['slug']); ?>">
                        <?php echo sanitize($cat['name']); ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card filter-item" data-category="<?php echo sanitize($product['category_name']); ?>" data-slug="<?php echo sanitize($product['slug'] ?? strtolower(str_replace(' ', '-', $product['category_name']))); ?>">
                        <div class="product-image">
                            <img src="<?php echo $product['image_url'] ?: 'https://via.placeholder.com/300x300?text=' . urlencode($product['name']); ?>" alt="<?php echo sanitize($product['name']); ?>">
                            <?php if (!$product['is_available']): ?>
                                <div class="out-of-stock">Out of Stock</div>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3><?php echo sanitize($product['name']); ?></h3>
                            <p class="category">
                                <i class="fas fa-tag"></i> <?php echo sanitize($product['category_name']); ?>
                            </p>
                            <p class="description"><?php echo sanitize(substr($product['description'], 0, 80)); ?></p>
                            <div class="price-stock">
                                <span class="price">$<?php echo number_format($product['price'], 2); ?></span>
                                <span class="stock">Stock: <?php echo $product['stock_quantity']; ?></span>
                            </div>
                            <button class="btn btn-add-cart" onclick="orderProduct(<?php echo $product['id']; ?>, '<?php echo sanitize($product['name']); ?>', <?php echo $product['price']; ?>)" <?php echo !$product['is_available'] ? 'disabled' : ''; ?>>
                                <i class="fas fa-shopping-cart"></i> Order Now
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Order Modal -->
    <div id="orderModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeOrderModal()">&times;</span>
            <h2 id="productNameModal">Product Name</h2>
            <form id="orderForm">
                <input type="hidden" id="productId" name="product_id">
                <input type="hidden" id="productPrice" name="product_price">

                <div class="form-group">
                    <label for="customerName">Your Name *</label>
                    <input type="text" id="customerName" name="customer_name" required>
                </div>

                <div class="form-group">
                    <label>WhatsApp Number *</label>
                    <div style="display: flex; gap: 10px;">
                        <select id="countryCode" style="width: 100px; padding: 10px; border: 1px solid var(--border-color); border-radius: 5px;" required>
                            <option value="+212">🇲🇦 Morocco (+212)</option>
                            <option value="+1">🇺🇸 USA/Canada (+1)</option>
                            <option value="+44">🇬🇧 UK (+44)</option>
                            <option value="+33">🇫🇷 France (+33)</option>
                            <option value="+34">🇪🇸 Spain (+34)</option>
                            <option value="+39">🇮🇹 Italy (+39)</option>
                            <option value="+49">🇩🇪 Germany (+49)</option>
                            <option value="+31">🇳🇱 Netherlands (+31)</option>
                            <option value="+32">🇧🇪 Belgium (+32)</option>
                            <option value="+41">🇨🇭 Switzerland (+41)</option>
                            <option value="+43">🇦🇹 Austria (+43)</option>
                            <option value="+46">🇸🇪 Sweden (+46)</option>
                            <option value="+47">🇳🇴 Norway (+47)</option>
                            <option value="+45">🇩🇰 Denmark (+45)</option>
                            <option value="+358">🇫🇮 Finland (+358)</option>
                            <option value="+48">🇵🇱 Poland (+48)</option>
                            <option value="+972">🇮🇱 Israel (+972)</option>
                            <option value="+966">🇸🇦 Saudi Arabia (+966)</option>
                            <option value="+971">🇦🇪 UAE (+971)</option>
                            <option value="+213">🇩🇿 Algeria (+213)</option>
                            <option value="+216">🇹🇳 Tunisia (+216)</option>
                            <option value="+212">🇲🇦 Morocco (+212)</option>
                            <option value="+20">🇪🇬 Egypt (+20)</option>
                        </select>
                        <input type="tel" id="customerPhone" name="customer_phone" placeholder="123456789" style="flex: 1;" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="customerEmail">Email (Optional)</label>
                    <input type="email" id="customerEmail" name="customer_email">
                </div>

                <div class="form-group">
                    <label for="quantity">Quantity *</label>
                    <input type="number" id="quantity" name="quantity" min="1" value="1" required>
                </div>

                <div class="form-group">
                    <label for="notes">Special Notes (Optional)</label>
                    <textarea id="notes" name="notes" rows="3"></textarea>
                </div>

                <div class="price-breakdown">
                    <div>Unit Price: <span id="unitPrice">$0.00</span></div>
                    <div style="font-weight: bold; font-size: 16px;">Total: <span id="totalPrice">$0.00</span></div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 20px;">
                    <i class="fas fa-check"></i> Place Order
                </button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2026 OML PARA. All rights reserved.</p>
        </div>
    </footer>

    <script src="products.js"></script>
    <script>
        let currentProductPrice = 0;

        function orderProduct(productId, productName, price) {
            currentProductPrice = price;
            document.getElementById('productId').value = productId;
            document.getElementById('productPrice').value = price;
            document.getElementById('productNameModal').textContent = productName;
            document.getElementById('unitPrice').textContent = '$' + price.toFixed(2);
            document.getElementById('quantity').value = 1;
            updateTotalPrice();
            document.getElementById('orderModal').style.display = 'block';
        }

        function closeOrderModal() {
            document.getElementById('orderModal').style.display = 'none';
        }

        function updateTotalPrice() {
            const quantity = parseInt(document.getElementById('quantity').value) || 1;
            const total = currentProductPrice * quantity;
            document.getElementById('totalPrice').textContent = '$' + total.toFixed(2);
        }

        document.getElementById('quantity').addEventListener('change', updateTotalPrice);
        document.getElementById('quantity').addEventListener('input', updateTotalPrice);

        document.getElementById('orderForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(document.getElementById('orderForm'));
            const data = Object.fromEntries(formData);
            
            // Combine country code with phone number
            const countryCode = document.getElementById('countryCode').value;
            const phone = document.getElementById('customerPhone').value.replace(/^0+/, ''); // remove leading zeros
            data.customer_phone = countryCode + phone;

            try {
                const response = await fetch('api/place-order.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    alert('Order placed successfully! Order ID: #' + result.order_id);
                    document.getElementById('orderForm').reset();
                    closeOrderModal();
                    
                    // Open WhatsApp link if available
                    if (result.whatsapp_url) {
                        setTimeout(() => {
                            window.open(result.whatsapp_url, '_blank');
                        }, 500);
                    }
                } else {
                    alert('Error: ' + (result.message || 'Failed to place order'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            }
        });

        window.onclick = function(event) {
            const modal = document.getElementById('orderModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }

        // if server provided a category filter, trigger the corresponding button
        <?php if ($categoryFilter):
            // find slug for the selected category
            $selected = '';
            foreach ($categories as $cat) {
                if ($cat['id'] == $categoryFilter) {
                    $selected = $cat['slug'];
                    break;
                }
            }
        ?>
        (function() {
            const slug = '<?php echo sanitize($selected); ?>';
            if (slug) {
                document.querySelectorAll('.filter-btn').forEach(btn => {
                    if (btn.dataset.filter === slug) {
                        btn.click();
                    }
                });
            }
        })();
        <?php endif; ?>
    </script>

    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 50px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 90%;
            max-width: 500px;
            border-radius: 10px;
            max-height: 90vh;
            overflow-y: auto;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: black;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            font-size: 14px;
            font-family: inherit;
        }

        .form-group textarea {
            resize: vertical;
        }

        .price-breakdown {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 4px solid var(--primary-color);
        }

        .price-breakdown div {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
        }

        .out-of-stock {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #e74c3c;
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 600;
        }

        .btn-add-cart {
            width: 100%;
            padding: 10px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-add-cart:hover:not(:disabled) {
            background: #27ae60;
        }

        .btn-add-cart:disabled {
            background: #95a5a6;
            cursor: not-allowed;
        }
    </style>
</body>
</html>
