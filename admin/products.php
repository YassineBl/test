<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/utils.php';
require_once __DIR__ . '/includes/init_db.php';

if (isset($_GET['diag'])) {
    header('Content-Type: text/plain; charset=UTF-8');
    echo "OK\n";
    echo "file=" . __FILE__ . "\n";
    echo "php=" . PHP_VERSION . "\n";
    echo "sapi=" . PHP_SAPI . "\n";
    exit;
}

if (isset($_GET['logout'])) {
    logout();
}

requireAdminLogin();

// Initialize database table
initProductImagesTable();

// Debug: Check if page is loading
$debug_mode = isset($_GET['debug']) && $_GET['debug'] === '1';

$action = $_GET['action'] ?? 'list';
$product_id = $_GET['id'] ?? null;
$message = $_GET['message'] ?? '';
$error = '';
$form_type = $_POST['form_type'] ?? '';

// Create uploads directory if it doesn't exist
$upload_dir = __DIR__ . '/../uploads/products/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

function uploadProductImageForProduct($pdo, $product_id, $file, $upload_dir, &$error, &$message, $successMessage = 'Image uploaded successfully!') {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $error = 'Upload error: ' . (int)($file['error'] ?? -1);
        return false;
    }

    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        $error = 'File size exceeds 5MB limit.';
        return false;
    }

    $tmp_path = $file['tmp_name'] ?? '';
    $mime_type = function_exists('mime_content_type') ? mime_content_type($tmp_path) : ($file['type'] ?? '');
    $allowed_types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];

    if (!isset($allowed_types[$mime_type])) {
        $error = 'Invalid file type. Only JPEG, PNG, GIF, and WebP images are allowed.';
        return false;
    }

    $extension = $allowed_types[$mime_type];
    $entropy = '';
    try {
        $entropy = bin2hex(random_bytes(4));
    } catch (Throwable $e) {
        $entropy = uniqid('', true);
        $entropy = preg_replace('/[^a-zA-Z0-9]/', '', (string)$entropy);
    }
    $filename = time() . '_' . $entropy . '.' . $extension;
    $filepath = $upload_dir . $filename;

    if (!move_uploaded_file($tmp_path, $filepath)) {
        $error = 'Error uploading file. Please try again.';
        return false;
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) as count FROM product_images WHERE product_id = ?');
    $stmt->execute([$product_id]);
    $result = $stmt->fetch();
    $current_count = (int)($result['count'] ?? 0);
    $is_primary = ($current_count === 0) ? 1 : 0;

    $stmt = $pdo->prepare('INSERT INTO product_images (product_id, image_filename, image_path, is_primary, display_order) VALUES (?, ?, ?, ?, ?)');
    if ($stmt->execute([$product_id, $filename, 'uploads/products/' . $filename, $is_primary, $current_count + 1])) {
        $message = $successMessage;
        return true;
    }

    @unlink($filepath);
    $error = 'Error saving image to database';
    return false;
}

// Handle image deletion
if (isset($_GET['delete_image'])) {
    $image_id = $_GET['delete_image'];
    $stmt = $pdo->prepare('SELECT image_filename FROM product_images WHERE id = ?');
    $stmt->execute([$image_id]);
    $image = $stmt->fetch();
    
    if ($image && file_exists($upload_dir . $image['image_filename'])) {
        unlink($upload_dir . $image['image_filename']);
    }
    
    $stmt = $pdo->prepare('DELETE FROM product_images WHERE id = ?');
    if ($stmt->execute([$image_id])) {
        $message = 'Image deleted successfully!';
    } else {
        $error = 'Error deleting image';
    }
    
    if ($product_id) {
        header("Location: products.php?action=edit&id=$product_id&message=" . urlencode($message));
        exit;
    }
}

// Handle image upload form from edit screen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $form_type === 'image_upload' && $product_id) {
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        uploadProductImageForProduct($pdo, $product_id, $_FILES['product_image'], $upload_dir, $error, $message, 'Image uploaded successfully!');
    } else {
        $error = 'Please choose an image first.';
    }
}

// Handle setting primary image
if (isset($_GET['set_primary']) && $product_id) {
    $image_id = $_GET['set_primary'];
    
    // First, unset all other primary images for this product
    $stmt = $pdo->prepare('UPDATE product_images SET is_primary = 0 WHERE product_id = ?');
    $stmt->execute([$product_id]);
    
    // Set this image as primary
    $stmt = $pdo->prepare('UPDATE product_images SET is_primary = 1 WHERE id = ? AND product_id = ?');
    if ($stmt->execute([$image_id, $product_id])) {
        $message = 'Primary image updated!';
    }
    
    header("Location: products.php?action=edit&id=$product_id");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $form_type === 'product_details') {
    $name = trim($_POST['name'] ?? '');
    $category_id = $_POST['category_id'] ?? '';
    $price = $_POST['price'] ?? '';
    $stock_quantity = $_POST['stock_quantity'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    $is_disabled = isset($_POST['is_disabled']) ? 1 : 0;
    $has_uploaded_image = isset($_FILES['product_image']) && $_FILES['product_image']['error'] !== UPLOAD_ERR_NO_FILE;

    // Normalize numeric inputs to avoid SQL errors on empty values
    $category_id = filter_var($category_id, FILTER_VALIDATE_INT);
    $price = is_numeric($price) ? (float)$price : null;
    $stock_quantity = ($stock_quantity === '' || !is_numeric($stock_quantity)) ? 0 : (int)$stock_quantity;

    if ($name === '' || $category_id === false || $price === null) {
        $error = 'Please fill in all required fields';
    } else {
        try {
            if ($action === 'add') {
                $stmt = $pdo->prepare('INSERT INTO products (category_id, name, description, price, stock_quantity, is_available, is_disabled) VALUES (?, ?, ?, ?, ?, ?, ?)');
                if ($stmt->execute([$category_id, $name, $description, $price, $stock_quantity, $is_available, $is_disabled])) {
                    $product_id = $pdo->lastInsertId();
                    $message = 'Product added successfully! Now you can add images.';
                    if ($has_uploaded_image) {
                        if (uploadProductImageForProduct($pdo, $product_id, $_FILES['product_image'], $upload_dir, $error, $message, 'Product and image added successfully!')) {
                            $message = 'Product and image added successfully!';
                        } else {
                            $error = 'Product added, but image upload failed: ' . $error;
                            $message = 'Product added successfully.';
                        }
                    }
                    $action = 'edit';
                } else {
                    $error = 'Error adding product';
                }
            } elseif ($action === 'edit' && $product_id) {
                $stmt = $pdo->prepare('UPDATE products SET category_id = ?, name = ?, description = ?, price = ?, stock_quantity = ?, is_available = ?, is_disabled = ? WHERE id = ?');
                if ($stmt->execute([$category_id, $name, $description, $price, $stock_quantity, $is_available, $is_disabled, $product_id])) {
                    $message = 'Product updated successfully!';
                    if ($has_uploaded_image) {
                        if (uploadProductImageForProduct($pdo, $product_id, $_FILES['product_image'], $upload_dir, $error, $message, 'Product updated and image uploaded successfully!')) {
                            $message = 'Product updated and image uploaded successfully!';
                        } else {
                            $error = 'Product updated, but image upload failed: ' . $error;
                        }
                    }
                } else {
                    $error = 'Error updating product';
                }
            } else {
                $error = 'Invalid product action';
            }
        } catch (Throwable $e) {
            error_log('Product save error: ' . $e->getMessage());
            $error = 'Database error while saving product.';
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    
    // Delete associated images
    $stmt = $pdo->prepare('SELECT image_filename FROM product_images WHERE product_id = ?');
    $stmt->execute([$delete_id]);
    $images = $stmt->fetchAll();
    
    foreach ($images as $img) {
        if (file_exists($upload_dir . $img['image_filename'])) {
            unlink($upload_dir . $img['image_filename']);
        }
    }
    
    // Delete product
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
    if ($stmt->execute([$delete_id])) {
        $message = 'Product deleted successfully!';
    } else {
        $error = 'Error deleting product';
    }
    $action = 'list';
}

// Get categories for dropdown
$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

// Get product if editing
$product = null;
$product_images = [];
if ($action === 'edit' && $product_id) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    if (!$product) {
        $error = 'Product not found';
        $action = 'list';
    } else {
        // Get product images
        $stmt = $pdo->prepare('SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, display_order ASC');
        $stmt->execute([$product_id]);
        $product_images = $stmt->fetchAll();
    }
}

// Get all products for list view
$products = [];
if ($action === 'list') {
    $products = $pdo->query('SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC')->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Management - OML PARA Admin</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { 
            background-color: #f5f6fa;
            margin: 0;
            padding: 0;
        }
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background: var(--dark-color);
            color: white;
            padding: 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        .sidebar .logo {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar ul { list-style: none; }
        .sidebar li { margin: 10px 0; }
        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 12px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        .sidebar a:hover,
        .sidebar a.active {
            background: var(--primary-color);
        }
        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 20px;
            min-height: 100vh;
            background-color: #f5f6fa;
        }
        .top-bar {
            background: white;
            padding: 20px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
        }
        .top-bar h1 { margin: 0; }
        .message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .error-msg {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .form-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: var(--shadow);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark-color);
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            font-size: 14px;
            font-family: inherit;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        .checkbox-group {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }
        .checkbox-group label {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .checkbox-group input {
            width: auto;
            margin: 0;
        }
        .form-buttons {
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-primary {
            background: var(--primary-color);
            color: white;
        }
        .btn-primary:hover {
            background: #27ae60;
        }
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        .btn-secondary:hover {
            background: #7f8c8d;
        }
        .products-table {
            background: white;
            border-radius: 10px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        .products-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .products-table th {
            background: var(--dark-color);
            color: white;
            padding: 15px;
            text-align: left;
        }
        .products-table td {
            padding: 15px;
            border-bottom: 1px solid var(--border-color);
        }
        .products-table tr:hover {
            background: #f9f9f9;
        }
        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-green {
            background: #d4edda;
            color: #155724;
        }
        .badge-red {
            background: #f8d7da;
            color: #721c24;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        .action-buttons a,
        .action-buttons button {
            padding: 5px 10px;
            font-size: 12px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
        }
        .action-buttons .edit {
            background: var(--secondary-color);
            color: white;
        }
        .action-buttons .delete {
            background: #e74c3c;
            color: white;
        }
        .action-buttons .edit:hover {
            background: #2980b9;
        }
        .action-buttons .delete:hover {
            background: #c0392b;
        }
        .product-images-section {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid var(--border-color);
        }
        .product-images-section h3 {
            color: var(--dark-color);
            margin-bottom: 20px;
        }
        .image-upload-area {
            border: 2px dashed var(--primary-color);
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            margin-bottom: 20px;
            background: #f0f8f5;
            cursor: pointer;
            transition: all 0.3s;
        }
        .image-upload-area:hover {
            border-color: #27ae60;
            background: #e8f5e9;
        }
        .image-upload-area.dragover {
            border-color: #27ae60;
            background: #e8f5e9;
            transform: scale(1.02);
        }
        .image-upload-area i {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        .image-upload-area p {
            margin: 0;
            color: var(--dark-color);
        }
        .image-upload-area input[type="file"] {
            display: none;
        }
        .images-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .image-card {
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        .image-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }
        .image-card-img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            display: block;
        }
        .image-card-info {
            padding: 10px;
            text-align: center;
            font-size: 12px;
        }
        .image-card-info .primary-badge {
            background: var(--primary-color);
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 8px;
        }
        .image-card-actions {
            display: flex;
            gap: 5px;
            padding: 10px;
            border-top: 1px solid var(--border-color);
            justify-content: center;
        }
        .image-card-actions button,
        .image-card-actions a {
            padding: 5px 8px;
            font-size: 11px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }
        .image-card-actions .set-primary {
            background: var(--secondary-color);
            color: white;
        }
        .image-card-actions .set-primary:hover {
            background: #2980b9;
        }
        .image-card-actions .delete-image {
            background: #e74c3c;
            color: white;
        }
        .image-card-actions .delete-image:hover {
            background: #c0392b;
        }
        .image-card-actions .set-primary:disabled {
            background: #95a5a6;
            cursor: not-allowed;
        }
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <i class="fas fa-shield-alt"></i> Admin Panel
            </div>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                <li><a href="products.php" class="active"><i class="fas fa-box"></i> Products</a></li>
                <li><a href="categories.php"><i class="fas fa-list"></i> Categories</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li><a href="?logout=true"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content" style="<?php echo $debug_mode ? 'border: 3px solid red;' : ''; ?>">
            <div class="top-bar">
                <h1><i class="fas fa-box"></i> Product Management</h1>
            </div>

            <?php if ($message): ?>
                <div class="message">
                    <span><?php echo $message; ?></span>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($action === 'list'): ?>
                <div class="top-bar" style="background: white; margin-bottom: 20px;">
                    <h2 style="margin: 0;">Products List (<?php echo count($products); ?> total)</h2>
                    <a href="products.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Product
                    </a>
                </div>

                <?php if (empty($products)): ?>
                    <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: var(--shadow); text-align: center; color: #666;">
                        <p><i class="fas fa-inbox" style="font-size: 2rem; color: #ccc;"></i></p>
                        <p>No products found. <a href="products.php?action=add">Add the first product</a></p>
                    </div>
                <?php else: ?>
                <div class="products-table">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $item): ?>
                                <tr>
                                    <td><?php echo $item['id']; ?></td>
                                    <td><?php echo sanitize($item['name']); ?></td>
                                    <td><?php echo sanitize($item['category_name']); ?></td>
                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                    <td><?php echo $item['stock_quantity']; ?></td>
                                    <td>
                                        <?php if ($item['is_disabled']): ?>
                                            <span class="badge badge-red">Disabled</span>
                                        <?php elseif ($item['is_available']): ?>
                                            <span class="badge badge-green">Available</span>
                                        <?php else: ?>
                                            <span class="badge badge-red">Unavailable</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="products.php?action=edit&id=<?php echo $item['id']; ?>" class="edit">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="products.php?delete=<?php echo $item['id']; ?>" class="delete" onclick="return confirm('Are you sure?');">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="form-container">
                    <h2><?php echo ($action === 'add') ? 'Add New Product' : 'Edit Product'; ?></h2>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="form_type" value="product_details">
                        <div class="form-group">
                            <label for="name">Product Name *</label>
                            <input type="text" id="name" name="name" required value="<?php echo $product ? sanitize($product['name']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="category_id">Category *</label>
                            <select id="category_id" name="category_id" required>
                                <option value="">Select a category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo ($product && $product['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                        <?php echo sanitize($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="price">Price ($) *</label>
                            <input type="number" id="price" name="price" step="0.01" required value="<?php echo $product ? $product['price'] : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="stock_quantity">Stock Quantity</label>
                            <input type="number" id="stock_quantity" name="stock_quantity" value="<?php echo $product ? $product['stock_quantity'] : '0'; ?>">
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description"><?php echo $product ? sanitize($product['description']) : ''; ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="product_image_main">Product Image (optional)</label>
                            <input type="file" id="product_image_main" name="product_image" accept="image/jpeg,image/png,image/gif,image/webp">
                        </div>

                        <div class="form-group">
                            <label>Availability & Status</label>
                            <div class="checkbox-group">
                                <label>
                                    <input type="checkbox" name="is_available" value="1" <?php echo ($product && $product['is_available']) ? 'checked' : ''; ?>>
                                    Available for Purchase
                                </label>
                                <label>
                                    <input type="checkbox" name="is_disabled" value="1" <?php echo ($product && $product['is_disabled']) ? 'checked' : ''; ?>>
                                    Disabled
                                </label>
                            </div>
                        </div>

                        <div class="form-buttons">
                            <button type="submit" class="btn btn-primary">
                                <?php echo ($action === 'add') ? 'Add Product' : 'Update Product'; ?>
                            </button>
                            <a href="products.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>

                    <?php if ($action === 'edit' && $product): ?>
                        <!-- Product Images Section -->
                        <div class="product-images-section">
                            <h3><i class="fas fa-images"></i> Product Photos</h3>
                            
                            <!-- Upload Area -->
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="form_type" value="image_upload">
                                <div class="image-upload-area" onclick="document.getElementById('imageInput').click()" style="cursor: pointer;">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <p><strong>Click to upload or drag & drop</strong></p>
                                    <p style="font-size: 12px; color: #666; margin-top: 5px;">JPG, PNG, GIF or WebP (Max 5MB)</p>
                                    <input type="file" id="imageInput" name="product_image" accept="image/*" onchange="this.form.submit()" style="display: none;">
                                </div>
                            </form>

                            <!-- Images Display -->
                            <?php if (!empty($product_images)): ?>
                                <div class="images-grid">
                                    <?php foreach ($product_images as $img): ?>
                                        <div class="image-card">
                                            <img src="../<?php echo sanitize($img['image_path']); ?>" alt="Product Image" class="image-card-img">
                                            <div class="image-card-info">
                                                <?php if ($img['is_primary']): ?>
                                                    <div class="primary-badge">Primary</div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="image-card-actions">
                                                <?php if (!$img['is_primary']): ?>
                                                    <a href="products.php?action=edit&id=<?php echo $product_id; ?>&set_primary=<?php echo $img['id']; ?>" class="set-primary" title="Set as Primary">
                                                        <i class="fas fa-star"></i> Set
                                                    </a>
                                                <?php else: ?>
                                                    <button type="button" class="set-primary" disabled title="Currently primary">
                                                        <i class="fas fa-star"></i> Primary
                                                    </button>
                                                <?php endif; ?>
                                                <a href="products.php?action=edit&id=<?php echo $product_id; ?>&delete_image=<?php echo $img['id']; ?>" class="delete-image" onclick="return confirm('Delete this image?');">
                                                    <i class="fas fa-trash"></i> Del
                                                </a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p style="color: #999; text-align: center; padding: 20px;"><i class="fas fa-image"></i> No images uploaded yet.</p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto-submit file upload when file is selected
        const imageInput = document.getElementById('imageInput');
        if (imageInput) {
            imageInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    const file = this.files[0];
                    const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    
                    if (!validTypes.includes(file.type)) {
                        alert('Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.');
                        this.value = '';
                        return;
                    }
                    
                    if (file.size > 5 * 1024 * 1024) {
                        alert('File size exceeds 5MB limit.');
                        this.value = '';
                        return;
                    }
                    
                    this.form.submit();
                }
            });
        }
    </script>
</body>
</html>
