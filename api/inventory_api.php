<?php
/**
 * Inventory API for PathLab Pro
 * Handles inventory management and low stock alerts
 */

// Prevent direct access
if (!defined('SECURE_ACCESS')) {
    define('SECURE_ACCESS', true);
}

// Include database configuration
require_once '../config.php';

// Set JSON header
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'low_stock_count':
            echo json_encode(getLowStockCount());
            break;
            
        case 'low_stock_items':
            echo json_encode(getLowStockItems());
            break;
            
        case 'inventory_stats':
            echo json_encode(getInventoryStats());
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Inventory API Error: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

function getLowStockCount() {
    try {
        // Try to connect to database
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASS,
            [PDO::ATTR_TIMEOUT => 3]
        );
        
        // Check if inventory table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'inventory'");
        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->query("
                SELECT COUNT(*) as count 
                FROM inventory 
                WHERE current_stock <= minimum_stock 
                AND is_active = 1
            ");
            $count = $stmt->fetch()['count'] ?? 0;
        } else {
            // Generate demo low stock count
            $count = generateDemoLowStockCount();
        }
        
        return [
            'success' => true,
            'data' => [
                'count' => (int)$count,
                'demo' => !isset($stmt) || $stmt->rowCount() === 0
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
    } catch (Exception $e) {
        // Return demo data
        return [
            'success' => true,
            'data' => [
                'count' => generateDemoLowStockCount(),
                'demo' => true
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}

function getLowStockItems() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASS,
            [PDO::ATTR_TIMEOUT => 3]
        );
        
        $stmt = $pdo->query("SHOW TABLES LIKE 'inventory'");
        if ($stmt->rowCount() > 0) {
            $stmt = $pdo->query("
                SELECT 
                    item_name,
                    current_stock,
                    minimum_stock,
                    unit,
                    last_updated
                FROM inventory 
                WHERE current_stock <= minimum_stock 
                AND is_active = 1
                ORDER BY (current_stock / minimum_stock) ASC
                LIMIT 20
            ");
            $items = $stmt->fetchAll();
        } else {
            $items = generateDemoLowStockItems();
        }
        
        return [
            'success' => true,
            'data' => $items,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
    } catch (Exception $e) {
        return [
            'success' => true,
            'data' => generateDemoLowStockItems(),
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}

function getInventoryStats() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
            DB_USER,
            DB_PASS,
            [PDO::ATTR_TIMEOUT => 3]
        );
        
        $stmt = $pdo->query("SHOW TABLES LIKE 'inventory'");
        if ($stmt->rowCount() > 0) {
            // Total items
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM inventory WHERE is_active = 1");
            $total = $stmt->fetch()['total'] ?? 0;
            
            // Low stock items
            $stmt = $pdo->query("
                SELECT COUNT(*) as low_stock 
                FROM inventory 
                WHERE current_stock <= minimum_stock AND is_active = 1
            ");
            $lowStock = $stmt->fetch()['low_stock'] ?? 0;
            
            // Out of stock
            $stmt = $pdo->query("
                SELECT COUNT(*) as out_of_stock 
                FROM inventory 
                WHERE current_stock = 0 AND is_active = 1
            ");
            $outOfStock = $stmt->fetch()['out_of_stock'] ?? 0;
            
        } else {
            $total = 156;
            $lowStock = generateDemoLowStockCount();
            $outOfStock = rand(0, 3);
        }
        
        return [
            'success' => true,
            'data' => [
                'total_items' => (int)$total,
                'low_stock' => (int)$lowStock,
                'out_of_stock' => (int)$outOfStock,
                'healthy_stock' => (int)($total - $lowStock),
                'demo' => !isset($stmt) || $stmt->rowCount() === 0
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
    } catch (Exception $e) {
        return [
            'success' => true,
            'data' => [
                'total_items' => 156,
                'low_stock' => generateDemoLowStockCount(),
                'out_of_stock' => rand(0, 3),
                'healthy_stock' => 145,
                'demo' => true
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}

function generateDemoLowStockCount() {
    // Generate realistic low stock count based on day of week
    $dayOfWeek = (int)date('w'); // 0 = Sunday, 6 = Saturday
    
    // More low stock items towards end of week
    if ($dayOfWeek >= 4) { // Thu-Sat
        return rand(3, 8);
    } elseif ($dayOfWeek >= 2) { // Tue-Wed
        return rand(1, 5);
    } else { // Sun-Mon (fresh stock)
        return rand(0, 3);
    }
}

function generateDemoLowStockItems() {
    $items = [
        ['item_name' => 'Blood Collection Tubes (EDTA)', 'current_stock' => 15, 'minimum_stock' => 50, 'unit' => 'pcs'],
        ['item_name' => 'Glucose Test Strips', 'current_stock' => 8, 'minimum_stock' => 25, 'unit' => 'strips'],
        ['item_name' => 'Disposable Syringes (5ml)', 'current_stock' => 12, 'minimum_stock' => 100, 'unit' => 'pcs'],
        ['item_name' => 'Lab Gloves (Nitrile)', 'current_stock' => 2, 'minimum_stock' => 10, 'unit' => 'boxes'],
        ['item_name' => 'Microscope Slides', 'current_stock' => 35, 'minimum_stock' => 100, 'unit' => 'pcs'],
        ['item_name' => 'Reagent Solution A', 'current_stock' => 150, 'minimum_stock' => 500, 'unit' => 'ml'],
        ['item_name' => 'Cover Slips', 'current_stock' => 25, 'minimum_stock' => 200, 'unit' => 'pcs'],
        ['item_name' => 'Alcohol Swabs', 'current_stock' => 3, 'minimum_stock' => 20, 'unit' => 'packs']
    ];
    
    $count = generateDemoLowStockCount();
    $selectedItems = array_slice($items, 0, $count);
    
    // Add timestamps
    foreach ($selectedItems as &$item) {
        $item['last_updated'] = date('Y-m-d H:i:s', strtotime('-' . rand(1, 48) . ' hours'));
        $item['demo'] = true;
    }
    
    return $selectedItems;
}
?>
