<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON payload']);
    exit;
}

$customer_name = $data['customer_name'] ?? '';
$customer_phone = $data['customer_phone'] ?? '';
$customer_email = $data['customer_email'] ?? '';
$product_id = isset($data['product_id']) ? (int) $data['product_id'] : 0;
$quantity = isset($data['quantity']) ? (int) $data['quantity'] : 1;
$product_price = $data['product_price'] ?? 0;
$notes = $data['notes'] ?? '';

// Validation
if (empty($customer_name) || empty($customer_phone) || $product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

if ($quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Quantity must be at least 1']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Verify product exists and lock row to avoid concurrent overselling
    $stmt = $pdo->prepare('SELECT id, stock_quantity, price, name FROM products WHERE id = ? AND is_disabled = 0 AND is_available = 1 FOR UPDATE');
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Product not found or unavailable']);
        exit;
    }

    if ((int) $product['stock_quantity'] < $quantity) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
        exit;
    }

    // Calculate total using trusted DB price
    $total_price = $product['price'] * $quantity;

    // Insert order
    $stmt = $pdo->prepare('
        INSERT INTO orders (customer_name, customer_phone, customer_email, product_id, quantity, total_price, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ');
    
    if ($stmt->execute([$customer_name, $customer_phone, $customer_email, $product_id, $quantity, $total_price, $notes])) {
        $order_id = $pdo->lastInsertId();

        // Update product stock
        $stmt = $pdo->prepare('UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?');
        $stmt->execute([$quantity, $product_id]);

        // Send WhatsApp message
        $phone = preg_replace('/[^0-9]/', '', $customer_phone);
        $whatsapp_url = null;
        
        if (strlen($phone) >= 10) {
            $whatsapp_message = "Hello {$customer_name},\n\n";
            $whatsapp_message .= "Your order for {$product['name']} (Qty: {$quantity}) has been received!\n";
            $whatsapp_message .= "Order ID: #{$order_id}\n";
            $whatsapp_message .= "Total: \${$total_price}\n";
            $whatsapp_message .= "Status: Pending\n\n";
            $whatsapp_message .= "We will confirm your order shortly. Thank you for shopping with OML PARA!";

            // Create WhatsApp link
            $whatsapp_url = WHATSAPP_API_URL . $phone . "&text=" . urlencode($whatsapp_message);

            // Mark as message sent since we're providing the link to customer
            $stmt = $pdo->prepare('UPDATE orders SET whatsapp_message_sent = 1 WHERE id = ?');
            $stmt->execute([$order_id]);
        }

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Order placed successfully!',
            'order_id' => $order_id,
            'total' => number_format($total_price, 2),
            'whatsapp_url' => $whatsapp_url
        ]);
    } else {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Failed to place order']);
    }
} catch(Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Order placement failed: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error while placing order']);
}
?>
