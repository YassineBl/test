<?php
/**
 * Database initialization utility
 * Run this once to set up the product_images table
 */

function initProductImagesTable() {
    global $pdo;
    
    if (!isset($pdo)) {
        return ['success' => false, 'message' => 'Database connection not available'];
    }
    
    try {
        // Check if table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'product_images'");
        if ($stmt->rowCount() > 0) {
            return ['success' => true, 'message' => 'Product images table already exists'];
        }
        
        // Create the table if it doesn't exist
        $sql = "
            CREATE TABLE IF NOT EXISTS product_images (
                id INT PRIMARY KEY AUTO_INCREMENT,
                product_id INT NOT NULL,
                image_path VARCHAR(255) NOT NULL,
                image_filename VARCHAR(255) NOT NULL,
                is_primary BOOLEAN DEFAULT 0,
                display_order INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                INDEX idx_product (product_id),
                INDEX idx_primary (is_primary)
            )
        ";
        
        $pdo->exec($sql);
        return ['success' => true, 'message' => 'Product images table created successfully'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}
