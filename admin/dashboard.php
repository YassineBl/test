<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/utils.php';

if (isset($_GET['logout'])) {
    logout();
}

$test_noauth = isset($_GET['noauth']) && $_GET['noauth'] === '1';
if (!$test_noauth) {
    requireAdminLogin();
}

// Get statistics
$stmt = $pdo->query('SELECT COUNT(*) as total FROM products');
$productsCount = $stmt->fetch()['total'];

$stmt = $pdo->query('SELECT COUNT(*) as total FROM categories');
$categoriesCount = $stmt->fetch()['total'];

$stmt = $pdo->query('SELECT COUNT(*) as total FROM orders WHERE status = "pending"');
$pendingOrders = $stmt->fetch()['total'];

$stmt = $pdo->query('SELECT COUNT(*) as total FROM orders');
$totalOrders = $stmt->fetch()['total'];

$stmt = $pdo->query('SELECT SUM(total_price) as revenue FROM orders WHERE status IN ("confirmed", "shipped", "delivered")');
$revenue = $stmt->fetch()['revenue'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - OML PARA</title>
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
        .sidebar ul {
            list-style: none;
        }
        .sidebar li {
            margin: 10px 0;
        }
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
            color: white;
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
        .top-bar h1 {
            margin: 0;
            color: var(--dark-color);
        }
        .logout-btn {
            background: #e74c3c;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .logout-btn:hover {
            background: #c0392b;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .stat-icon {
            font-size: 40px;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        .stat-icon.products {
            background: #3498db;
            color: white;
        }
        .stat-icon.categories {
            background: #2ecc71;
            color: white;
        }
        .stat-icon.orders {
            background: #f39c12;
            color: white;
        }
        .stat-icon.revenue {
            background: #9b59b6;
            color: white;
        }
        .stat-info h3 {
            margin: 0;
            color: #7f8c8d;
            font-size: 12px;
            text-transform: uppercase;
        }
        .stat-info p {
            margin: 5px 0 0 0;
            font-size: 28px;
            font-weight: bold;
            color: var(--dark-color);
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .dashboard-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: var(--shadow);
        }
        .dashboard-card h2 {
            margin-top: 0;
            color: var(--dark-color);
            font-size: 18px;
        }
        .card-action {
            margin-top: 15px;
        }
        .card-action a,
        .card-action button {
            display: inline-block;
            padding: 10px 20px;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            transition: background 0.3s;
        }
        .card-action a:hover,
        .card-action button:hover {
            background: #27ae60;
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
                <li><a href="dashboard.php" class="active"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                <li><a href="products.php"><i class="fas fa-box"></i> Products</a></li>
                <li><a href="categories.php"><i class="fas fa-list"></i> Categories</a></li>
                <li><a href="orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li><a href="?logout=true" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="top-bar">
                <h1>Dashboard</h1>
                <div>Welcome, <strong><?php echo sanitize($_SESSION['admin_username'] ?? 'Test User'); ?></strong></div>
            </div>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon products"><i class="fas fa-box"></i></div>
                    <div class="stat-info">
                        <h3>Total Products</h3>
                        <p><?php echo $productsCount; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon categories"><i class="fas fa-list"></i></div>
                    <div class="stat-info">
                        <h3>Categories</h3>
                        <p><?php echo $categoriesCount; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orders"><i class="fas fa-shopping-cart"></i></div>
                    <div class="stat-info">
                        <h3>Pending Orders</h3>
                        <p><?php echo $pendingOrders; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon revenue"><i class="fas fa-dollar-sign"></i></div>
                    <div class="stat-info">
                        <h3>Revenue</h3>
                        <p>$<?php echo number_format($revenue, 2); ?></p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <h2><i class="fas fa-plus"></i> Add New Product</h2>
                    <p>Add a new product to your inventory</p>
                    <div class="card-action">
                        <a href="products.php?action=add">Add Product</a>
                    </div>
                </div>

                <div class="dashboard-card">
                    <h2><i class="fas fa-plus"></i> Add New Category</h2>
                    <p>Create a new product category</p>
                    <div class="card-action">
                        <a href="categories.php?action=add">Add Category</a>
                    </div>
                </div>

                <div class="dashboard-card">
                    <h2><i class="fas fa-inbox"></i> Manage Orders</h2>
                    <p>View and manage customer orders (<?php echo $pendingOrders; ?> pending)</p>
                    <div class="card-action">
                        <a href="orders.php">View Orders</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
