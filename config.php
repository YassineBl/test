<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Change if you have a password
define('DB_NAME', 'oml_para');

// WhatsApp Configuration
define('WHATSAPP_PHONE', '212663588682'); // Your WhatsApp business number
define('WHATSAPP_API_URL', 'https://api.whatsapp.com/send?phone='); // WhatsApp API endpoint

// Admin Configuration
define('ADMIN_PATH', '/admin/');

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Helper Functions
function sanitize($input) {
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function getBaseUrl() {
    $baseUrl = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $baseUrl .= "://" . $_SERVER['HTTP_HOST'];
    return $baseUrl;
}
?>
