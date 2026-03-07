<?php
/**
 * Product Image System Setup
 * Access this page to initialize the database for product images
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/init_db.php';

$initResult = initProductImagesTable();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Images Setup - OML PARA Admin</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .setup-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            width: 100%;
            margin: 20px;
        }
        .setup-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .setup-header h1 {
            color: var(--dark-color);
            margin: 0 0 10px 0;
        }
        .setup-header p {
            color: #666;
            margin: 0;
        }
        .status-box {
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .status-box.success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .status-box.error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .status-box i {
            font-size: 24px;
            flex-shrink: 0;
        }
        .status-message {
            flex: 1;
        }
        .status-message p {
            margin: 0;
            font-weight: 500;
        }
        .checklist {
            margin: 30px 0;
            list-style: none;
            padding: 0;
        }
        .checklist li {
            padding: 12px;
            border-left: 3px solid var(--primary-color);
            margin: 10px 0;
            background: #f9f9f9;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .checklist i {
            color: var(--primary-color);
            font-weight: bold;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        .btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
        }
        .btn-primary {
            background: var(--primary-color);
            color: white;
        }
        .btn-primary:hover {
            background: #27ae60;
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        .btn-secondary:hover {
            background: #7f8c8d;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <div class="setup-header">
            <h1><i class="fas fa-images"></i> Product Images System</h1>
            <p>Setup & Initialization</p>
        </div>

        <?php if ($initResult['success']): ?>
            <div class="status-box success">
                <i class="fas fa-check-circle"></i>
                <div class="status-message">
                    <p>✓ Database Ready</p>
                    <small><?php echo sanitize($initResult['message']); ?></small>
                </div>
            </div>

            <div style="background: #f0f8f5; padding: 20px; border-radius: 8px; margin: 20px 0;">
                <h3 style="color: var(--dark-color); margin-top: 0;">Ready to Use!</h3>
                <p style="color: #666; margin: 0;">The product image management system is now ready. You can:</p>
                <ul style="color: #555; margin: 10px 0; padding-left: 20px;">
                    <li>Upload multiple images per product</li>
                    <li>Set primary/featured images</li>
                    <li>Delete images individually</li>
                    <li>Better product presentation</li>
                </ul>
            </div>
        <?php else: ?>
            <div class="status-box error">
                <i class="fas fa-exclamation-circle"></i>
                <div class="status-message">
                    <p>⚠ Setup Issue</p>
                    <small><?php echo sanitize($initResult['message']); ?></small>
                </div>
            </div>

            <div style="background: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0; color: #856404;">
                <strong>Please ensure:</strong>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Database connection is working</li>
                    <li>Upload directory has write permissions</li>
                    <li>Try again or contact support</li>
                </ul>
            </div>
        <?php endif; ?>

        <div class="checklist">
            <li>
                <i class="fas fa-check"></i>
                <span><strong>Database Table</strong> - product_images table configured</span>
            </li>
            <li>
                <i class="fas fa-check"></i>
                <span><strong>Upload Directory</strong> - /uploads/products auto-created on first upload</span>
            </li>
            <li>
                <i class="fas fa-check"></i>
                <span><strong>File Validation</strong> - JPG, PNG, GIF, WebP (max 5MB)</span>
            </li>
            <li>
                <i class="fas fa-check"></i>
                <span><strong>Features</strong> - Drag & drop, primary image, bulk delete</span>
            </li>
        </div>

        <div class="action-buttons">
            <a href="products.php" class="btn btn-primary">
                <i class="fas fa-arrow-right"></i> Go to Products
            </a>
            <a href="dashboard.php" class="btn btn-secondary">
                <i class="fas fa-home"></i> Dashboard
            </a>
        </div>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #999;">
            <p style="margin: 0;">For detailed information, see <strong>PRODUCT_IMAGES_GUIDE.md</strong></p>
        </div>
    </div>
</body>
</html>
