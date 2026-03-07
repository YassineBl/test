<?php
// Utility Functions for the application

/**
 * Format price to currency
 */
function formatPrice($price) {
    return '$' . number_format($price, 2);
}

/**
 * Get status badge color
 */
function getStatusBadgeClass($status) {
    $classes = [
        'pending' => 'badge-pending',
        'confirmed' => 'badge-confirmed',
        'shipped' => 'badge-shipped',
        'delivered' => 'badge-delivered',
        'cancelled' => 'badge-cancelled'
    ];
    return $classes[$status] ?? 'badge-pending';
}

/**
 * Get order status color (for charts/reports)
 */
function getStatusColor($status) {
    $colors = [
        'pending' => '#fff3cd',
        'confirmed' => '#cfe2ff',
        'shipped' => '#d1ecf1',
        'delivered' => '#d4edda',
        'cancelled' => '#f8d7da'
    ];
    return $colors[$status] ?? '#ecf0f1';
}

/**
 * Generate slug from string
 */
function generateSlug($string) {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string), '-'));
}

/**
 * Validate phone number
 */
function validatePhoneNumber($phone) {
    $cleaned = preg_replace('/[^0-9]/', '', $phone);
    return strlen($cleaned) >= 10 && strlen($cleaned) <= 15;
}

/**
 * Format phone number for WhatsApp
 */
function formatPhoneForWhatsApp($phone) {
    return preg_replace('/[^0-9]/', '', $phone);
}

/**
 * Generate WhatsApp share link
 */
function generateWhatsAppLink($phone, $message) {
    $cleanPhone = formatPhoneForWhatsApp($phone);
    return "https://api.whatsapp.com/send?phone=" . $cleanPhone . "&text=" . urlencode($message);
}

/**
 * Send email notification (requires mail server setup)
 */
function sendEmailNotification($to, $subject, $message) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
    $headers .= "From: noreply@omlpara.com" . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}

/**
 * Get icon for category
 */
function getCategoryIcon($categoryName) {
    $icons = [
        'skincare' => 'fas fa-leaf',
        'supplements' => 'fas fa-pills',
        'natural' => 'fas fa-seedling',
        'firstaid' => 'fas fa-bandage',
        'beauty' => 'fas fa-spa',
    ];
    
    $slug = strtolower(str_replace(' ', '', $categoryName));
    return $icons[$slug] ?? 'fas fa-box';
}

/**
 * Generate order summary for email/WhatsApp
 */
function generateOrderSummary($order, $product) {
    $summary = "📋 **ORDER SUMMARY**\n\n";
    $summary .= "Order ID: #" . $order['id'] . "\n";
    $summary .= "Product: " . $product['name'] . "\n";
    $summary .= "Quantity: " . $order['quantity'] . "\n";
    $summary .= "Unit Price: \$" . number_format($product['price'], 2) . "\n";
    $summary .= "Total: \$" . number_format($order['total_price'], 2) . "\n";
    $summary .= "Status: " . ucfirst($order['status']) . "\n\n";
    $summary .= "Thank you for shopping with OML PARA!";
    
    return $summary;
}

?>
