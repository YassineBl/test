<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/utils.php';

if (isset($_GET['logout'])) {
    logout();
}

requireAdminLogin();

$action = $_GET['action'] ?? 'list';
$category_id = $_GET['id'] ?? null;
$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $slug = $_POST['slug'] ?? '';
    $description = $_POST['description'] ?? '';

    if (empty($name)) {
        $error = 'Please enter category name';
    } else {
        if (!$slug) {
            $slug = strtolower(str_replace(' ', '-', $name));
        }

        if ($action === 'add') {
            $stmt = $pdo->prepare('INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)');
            if ($stmt->execute([$name, $slug, $description])) {
                $message = 'Category added successfully!';
                $action = 'list';
            } else {
                $error = 'Error adding category (may already exist)';
            }
        } elseif ($action === 'edit' && $category_id) {
            $stmt = $pdo->prepare('UPDATE categories SET name = ?, slug = ?, description = ? WHERE id = ?');
            if ($stmt->execute([$name, $slug, $description, $category_id])) {
                $message = 'Category updated successfully!';
                $action = 'list';
            } else {
                $error = 'Error updating category';
            }
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $stmt = $pdo->prepare('DELETE FROM categories WHERE id = ?');
    if ($stmt->execute([$delete_id])) {
        $message = 'Category deleted successfully!';
    } else {
        $error = 'Error deleting category (has associated products)';
    }
    $action = 'list';
}

// Get category if editing
$category = null;
if ($action === 'edit' && $category_id) {
    $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->execute([$category_id]);
    $category = $stmt->fetch();
    if (!$category) {
        $error = 'Category not found';
        $action = 'list';
    }
}

// Get all categories for list view
$categories = [];
if ($action === 'list') {
    $categories = $pdo->query('SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.created_at DESC')->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories Management - OML PARA Admin</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f5f6fa; }
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
            min-height: 100px;
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
        .categories-table {
            background: white;
            border-radius: 10px;
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        .categories-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .categories-table th {
            background: var(--dark-color);
            color: white;
            padding: 15px;
            text-align: left;
        }
        .categories-table td {
            padding: 15px;
            border-bottom: 1px solid var(--border-color);
        }
        .categories-table tr:hover {
            background: #f9f9f9;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
        }
        .action-buttons a {
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
    </style>
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
                <li><a href="products.php"><i class="fas fa-box"></i> Products</a></li>
                <li><a href="categories.php" class="active"><i class="fas fa-list"></i> Categories</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li><a href="?logout=true"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="top-bar">
                <h1><i class="fas fa-list"></i> Category Management</h1>
            </div>

            <?php if ($message): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($action === 'list'): ?>
                <div class="top-bar" style="background: white; margin-bottom: 20px;">
                    <h2 style="margin: 0;">Categories List</h2>
                    <a href="categories.php?action=add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Category
                    </a>
                </div>

                <div class="categories-table">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Products</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td><?php echo $cat['id']; ?></td>
                                    <td><?php echo sanitize($cat['name']); ?></td>
                                    <td><?php echo sanitize($cat['slug']); ?></td>
                                    <td><?php echo $cat['product_count']; ?></td>
                                    <td><?php echo sanitize(substr($cat['description'], 0, 50)); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="categories.php?action=edit&id=<?php echo $cat['id']; ?>" class="edit">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="categories.php?delete=<?php echo $cat['id']; ?>" class="delete" onclick="return confirm('Are you sure?');">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php else: ?>
                <div class="form-container">
                    <h2><?php echo ($action === 'add') ? 'Add New Category' : 'Edit Category'; ?></h2>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label for="name">Category Name *</label>
                            <input type="text" id="name" name="name" required value="<?php echo $category ? sanitize($category['name']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="slug">Slug (URL friendly)</label>
                            <input type="text" id="slug" name="slug" value="<?php echo $category ? sanitize($category['slug']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description"><?php echo $category ? sanitize($category['description']) : ''; ?></textarea>
                        </div>

                        <div class="form-buttons">
                            <button type="submit" class="btn btn-primary">
                                <?php echo ($action === 'add') ? 'Add Category' : 'Update Category'; ?>
                            </button>
                            <a href="categories.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
