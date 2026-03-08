<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/utils.php';

if (isset($_GET['logout'])) {
    logout();
}

requireAdminLogin();

$action = $_GET['action'] ?? 'list';
$order_id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$message = '';
$error = '';

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'] ?? '';
    $order_id = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;
    $allowed_statuses = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];

    if (!in_array($status, $allowed_statuses, true) || $order_id <= 0) {
        $error = 'Invalid request';
    } else {
        $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
        if ($stmt->execute([$status, $order_id])) {
            $message = 'Order status updated successfully!';
        } else {
            $error = 'Error updating order status';
        }
        $action = 'list';
    }
}

// Handle WhatsApp notification
if (isset($_GET['send_whatsapp'])) {
    $send_id = (int) $_GET['send_whatsapp'];
    if ($send_id <= 0) {
        $error = 'Invalid order ID';
    } else {
    $stmt = $pdo->prepare('SELECT o.*, p.name as product_name FROM orders o LEFT JOIN products p ON o.product_id = p.id WHERE o.id = ?');
    $stmt->execute([$send_id]);
    $order = $stmt->fetch();

    if ($order) {
        $phone = preg_replace('/[^0-9]/', '', $order['customer_phone']);
        if (strlen($phone) < 10) {
            $error = 'Invalid phone number';
        } else {
            // WhatsApp message
            $whatsapp_message = "Hello {$order['customer_name']},\n\n";
            $whatsapp_message .= "Your order for {$order['product_name']} (Qty: {$order['quantity']}) has been received.\n";
            $whatsapp_message .= "Order Total: \${$order['total_price']}\n";
            $whatsapp_message .= "Status: " . ucfirst($order['status']) . "\n\n";
            $whatsapp_message .= "Thank you for shopping with OML PARA!";

            // Create WhatsApp share link
            $whatsapp_url = WHATSAPP_API_URL . $phone . "&text=" . urlencode($whatsapp_message);

            // Update database to mark message as sent
            $stmt = $pdo->prepare('UPDATE orders SET whatsapp_message_sent = 1 WHERE id = ?');
            $stmt->execute([$send_id]);

            $message = 'WhatsApp link generated! Customer will receive a message when they click the link.';
            // The link will be shown to the admin to copy/send
        }
    } else {
        $error = 'Order not found';
    }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $delete_id = (int) $_GET['delete'];
    if ($delete_id <= 0) {
        $error = 'Invalid order ID';
    } else {
        $stmt = $pdo->prepare('DELETE FROM orders WHERE id = ?');
        if ($stmt->execute([$delete_id])) {
            $message = 'Order deleted successfully!';
        } else {
            $error = 'Error deleting order';
        }
    }
    $action = 'list';
}

// Get order if viewing details
$order = null;
if ($action === 'view' && $order_id) {
    $stmt = $pdo->prepare('SELECT o.*, p.name as product_name, p.price as product_price, c.name as category_name FROM orders o LEFT JOIN products p ON o.product_id = p.id LEFT JOIN categories c ON p.category_id = c.id WHERE o.id = ?');
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();
    if (!$order) {
        $error = 'Order not found';
        $action = 'list';
    }
}

// Get all orders for list view
$orders = [];
if ($action === 'list') {
    $orders = $pdo->query('SELECT o.id, o.customer_name, o.customer_phone, o.quantity, o.total_price, o.status, o.whatsapp_message_sent, o.created_at, p.name as product_name FROM orders o LEFT JOIN products p ON o.product_id = p.id ORDER BY o.created_at DESC')->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management - OML PARA Admin</title>
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
        .orders-table {
            background: white;
            border-radius: 10px;
            box-shadow: var(--shadow);
            overflow: auto;
        }
        .orders-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .orders-table th {
            background: var(--dark-color);
            color: white;
            padding: 15px;
            text-align: left;
        }
        .orders-table td {
            padding: 15px;
            border-bottom: 1px solid var(--border-color);
        }
        .orders-table tr:hover {
            background: #f9f9f9;
        }
        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-confirmed { background: #cfe2ff; color: #084298; }
        .badge-shipped { background: #d1ecf1; color: #0c5460; }
        .badge-delivered { background: #d4edda; color: #155724; }
        .badge-cancelled { background: #f8d7da; color: #721c24; }
        .action-buttons {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        .action-buttons a,
        .action-buttons button {
            padding: 5px 10px;
            font-size: 11px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
        }
        .action-buttons .view {
            background: var(--secondary-color);
            color: white;
        }
        .action-buttons .whatsapp {
            background: #25d366;
            color: white;
        }
        .action-buttons .delete {
            background: #e74c3c;
            color: white;
        }
        .action-buttons .view:hover { background: #2980b9; }
        .action-buttons .whatsapp:hover { background: #1ead56; }
        .action-buttons .delete:hover { background: #c0392b; }
        .detail-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: var(--shadow);
        }
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .detail-item h3 {
            margin-top: 0;
            color: var(--dark-color);
            font-size: 12px;
            text-transform: uppercase;
            color: #7f8c8d;
        }
        .detail-item p {
            margin: 0;
            font-size: 16px;
            color: var(--dark-color);
        }
        .status-form {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
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
        .whatsapp-link {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
        }
        .whatsapp-link a {
            color: #155724;
            text-decoration: none;
            font-weight: 600;
            word-break: break-all;
        }
        .whatsapp-link a:hover {
            text-decoration: underline;
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
                <li><a href="categories.php"><i class="fas fa-list"></i> Categories</a></li>
                <li><a href="orders.php" class="active"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li><a href="?logout=true"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="top-bar">
                <h1><i class="fas fa-shopping-cart"></i> Order Management</h1>
            </div>

            <?php if ($message): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($action === 'list'): ?>
                <div class="orders-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Product</th>
                                <th>Qty</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>WhatsApp</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $item): ?>
                                <tr>
                                    <td>#<?php echo $item['id']; ?></td>
                                    <td><?php echo sanitize($item['customer_name']); ?><br><small><?php echo sanitize($item['customer_phone']); ?></small></td>
                                    <td><?php echo sanitize($item['product_name']); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>$<?php echo number_format($item['total_price'], 2); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $item['status']; ?>">
                                            <?php echo ucfirst($item['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($item['whatsapp_message_sent']): ?>
                                            <span style="color: #25d366;"><i class="fas fa-check-circle"></i> Sent</span>
                                        <?php else: ?>
                                            <span style="color: #999;"><i class="fas fa-circle"></i> Not sent</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($item['created_at'])); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="orders.php?action=view&id=<?php echo $item['id']; ?>" class="view">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <a href="orders.php?send_whatsapp=<?php echo $item['id']; ?>" class="whatsapp">
                                                <i class="fab fa-whatsapp"></i> WhatsApp
                                            </a>
                                            <a href="orders.php?delete=<?php echo $item['id']; ?>" class="delete" onclick="return confirm('Are you sure?');">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($action === 'view' && $order): ?>
                <div class="detail-container">
                    <h2>Order #<?php echo $order['id']; ?></h2>

                    <div class="detail-grid">
                        <div class="detail-item">
                            <h3>Customer Name</h3>
                            <p><?php echo sanitize($order['customer_name']); ?></p>
                        </div>
                        <div class="detail-item">
                            <h3>Customer Phone</h3>
                            <p><?php echo sanitize($order['customer_phone']); ?></p>
                        </div>
                        <div class="detail-item">
                            <h3>Customer Email</h3>
                            <p><?php echo sanitize($order['customer_email'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="detail-item">
                            <h3>Order Status</h3>
                            <p><span class="badge badge-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></p>
                        </div>
                        <div class="detail-item">
                            <h3>Product</h3>
                            <p><?php echo sanitize($order['product_name']); ?></p>
                        </div>
                        <div class="detail-item">
                            <h3>Quantity</h3>
                            <p><?php echo $order['quantity']; ?></p>
                        </div>
                        <div class="detail-item">
                            <h3>Unit Price</h3>
                            <p>$<?php echo number_format($order['product_price'], 2); ?></p>
                        </div>
                        <div class="detail-item">
                            <h3>Total Price</h3>
                            <p><strong>$<?php echo number_format($order['total_price'], 2); ?></strong></p>
                        </div>
                        <div class="detail-item">
                            <h3>Order Date</h3>
                            <p><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></p>
                        </div>
                        <div class="detail-item">
                            <h3>WhatsApp Sent</h3>
                            <p><?php echo $order['whatsapp_message_sent'] ? 'Yes' : 'No'; ?></p>
                        </div>
                    </div>

                    <?php if ($order['notes']): ?>
                        <div class="detail-item" style="margin-bottom: 20px;">
                            <h3>Notes</h3>
                            <p><?php echo sanitize($order['notes']); ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Status Update Form -->
                    <div class="status-form">
                        <h3>Update Order Status</h3>
                        <form method="POST">
                            <div class="form-group">
                                <label for="status">Select New Status</label>
                                <select name="status" id="status" required>
                                    <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="confirmed" <?php echo $order['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                    <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                    <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                    <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">Update Status</button>
                        </form>
                    </div>

                    <!-- WhatsApp Link Generator -->
                    <?php
                    if (isset($_GET['send_whatsapp']) && $_GET['send_whatsapp'] == $order['id']) {
                        $phone = preg_replace('/[^0-9]/', '', $order['customer_phone']);
                        if (strlen($phone) >= 10) {
                            $whatsapp_message = "Hello {$order['customer_name']},\n\n";
                            $whatsapp_message .= "Your order for {$order['product_name']} (Qty: {$order['quantity']}) has been received.\n";
                            $whatsapp_message .= "Order Total: \${$order['total_price']}\n";
                            $whatsapp_message .= "Status: " . ucfirst($order['status']) . "\n\n";
                            $whatsapp_message .= "Thank you for shopping with OML PARA!";
                            $whatsapp_url = "https://api.whatsapp.com/send?phone=" . $phone . "&text=" . urlencode($whatsapp_message);
                            ?>
                            <div class="whatsapp-link">
                                <h3><i class="fab fa-whatsapp"></i> WhatsApp Link</h3>
                                <p>Click below to send WhatsApp message to customer:</p>
                                <a href="<?php echo $whatsapp_url; ?>" target="_blank">
                                    <i class="fab fa-whatsapp"></i> Send via WhatsApp
                                </a>
                            </div>
                            <?php
                        }
                    }
                    ?>

                    <div style="margin-top: 20px;">
                        <a href="orders.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Orders</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
