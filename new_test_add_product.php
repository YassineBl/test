<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/admin/includes/init_db.php';

$message = '';
$error = '';
$upload_dir = __DIR__ . '/uploads/products/';

initProductImagesTable();

if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category_id = $_POST['category_id'] ?? '';
    $price = $_POST['price'] ?? '';

    $category_id = filter_var($category_id, FILTER_VALIDATE_INT);
    $price = is_numeric($price) ? (float)$price : null;

    if ($name === '' || $category_id === false || $price === null) {
        $error = 'Please provide valid Name, Category, and Price.';
    } else {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO products (category_id, name, description, price, stock_quantity, is_available, is_disabled)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );

            $ok = $stmt->execute([$category_id, $name, '', $price, 0, 1, 0]);
            if ($ok) {
                $product_id = (int)$pdo->lastInsertId();

                // Optional image upload
                if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                    if ($_FILES['product_image']['error'] !== UPLOAD_ERR_OK) {
                        throw new RuntimeException('Image upload failed with error code ' . (int)$_FILES['product_image']['error']);
                    }

                    if ($_FILES['product_image']['size'] > (5 * 1024 * 1024)) {
                        throw new RuntimeException('Image exceeds 5MB limit.');
                    }

                    $tmp = $_FILES['product_image']['tmp_name'];
                    $mime = mime_content_type($tmp) ?: '';
                    $allowed = [
                        'image/jpeg' => 'jpg',
                        'image/png' => 'png',
                        'image/gif' => 'gif',
                        'image/webp' => 'webp',
                    ];

                    if (!isset($allowed[$mime])) {
                        throw new RuntimeException('Invalid image type. Use JPG, PNG, GIF, or WebP.');
                    }

                    $filename = time() . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
                    $dest = $upload_dir . $filename;

                    if (!move_uploaded_file($tmp, $dest)) {
                        throw new RuntimeException('Failed to save uploaded image.');
                    }

                    $imgStmt = $pdo->prepare(
                        'INSERT INTO product_images (product_id, image_filename, image_path, is_primary, display_order)
                         VALUES (?, ?, ?, ?, ?)'
                    );
                    $imgStmt->execute([$product_id, $filename, 'uploads/products/' . $filename, 1, 1]);
                }

                $message = 'Test product created. ID: ' . $product_id;
            } else {
                $error = 'Insert failed.';
            }
        } catch (Throwable $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Add Product</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 24px; background: #f6f7f9; }
        .card { max-width: 620px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #e5e7eb; }
        h1 { margin-top: 0; }
        .field { margin-bottom: 14px; }
        label { display: block; margin-bottom: 6px; font-weight: 600; }
        input, select { width: 100%; box-sizing: border-box; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; }
        button { padding: 10px 14px; border: 0; border-radius: 6px; background: #2563eb; color: #fff; cursor: pointer; }
        .ok { margin-bottom: 14px; padding: 10px; border-radius: 6px; background: #dcfce7; color: #166534; }
        .err { margin-bottom: 14px; padding: 10px; border-radius: 6px; background: #fee2e2; color: #991b1b; }
        .note { margin-top: 12px; color: #4b5563; font-size: 14px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Test Add Product</h1>
        <p>Add a product using only name, category, and price.</p>

        <?php if ($message): ?>
            <div class="ok"><?php echo sanitize($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="err"><?php echo sanitize($error); ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="field">
                <label for="name">Name *</label>
                <input id="name" name="name" type="text" required>
            </div>

            <div class="field">
                <label for="category_id">Category *</label>
                <select id="category_id" name="category_id" required>
                    <option value="">Select a category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo (int)$cat['id']; ?>">
                            <?php echo sanitize($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="field">
                <label for="price">Price *</label>
                <input id="price" name="price" type="number" min="0" step="0.01" required>
            </div>

            <div class="field">
                <label for="product_image">Image (optional)</label>
                <input id="product_image" name="product_image" type="file" accept="image/jpeg,image/png,image/gif,image/webp">
            </div>

            <button type="submit">Create Test Product</button>
        </form>

        <div class="note">
            Defaults used: description empty, stock 0, available 1, disabled 0. Optional image max size: 5MB.
        </div>
    </div>
</body>
</html>
