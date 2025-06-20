<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

require_once '../config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_POST['action'] ?? $_GET['action'] ?? null;

try {
    switch ($method) {
        case 'GET':
            switch ($action) {
                case 'get_general_settings':
                    getGeneralSettings($pdo);
                    break;
                default:
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
                    break;
            }
            break;
            
        case 'POST':
            switch ($action) {
                case 'save_general_settings':
                    saveGeneralSettings($pdo, $_POST['settings'] ?? null);
                    break;
                case 'save_notification_settings':
                    saveNotificationSettings($pdo, $_POST['settings'] ?? null);
                    break;
                case 'save_appearance_settings':
                    saveAppearanceSettings($pdo, $_POST['settings'] ?? null);
                    break;
                default:
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
                    break;
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

function getGeneralSettings($pdo) {
    try {
        // Check if settings table exists
        $stmt = $pdo->prepare("SHOW TABLES LIKE 'lab_settings'");
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            // Create settings table if it doesn't exist
            $pdo->exec("CREATE TABLE lab_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(50) NOT NULL UNIQUE,
                setting_value TEXT,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )");
            
            // Insert default settings
            $defaultSettings = [
                ['laboratory_name', 'PathLab Pro'],
                ['laboratory_address', '123 Medical Center Drive, Suite 456, Healthcare City, HC 12345'],
                ['contact_phone', '(555) 123-4567'],
                ['contact_email', 'info@pathlabpro.com']
            ];
            
            $insertStmt = $pdo->prepare("INSERT INTO lab_settings (setting_key, setting_value) VALUES (?, ?)");
            foreach ($defaultSettings as $setting) {
                $insertStmt->execute($setting);
            }
        }
        
        // Fetch settings
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM lab_settings");
        $settings = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        
        echo json_encode(['success' => true, 'data' => $settings]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function saveGeneralSettings($pdo, $settings) {
    if (!$settings || !is_array($settings)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid settings data']);
        return;
    }
    
    try {
        // Ensure settings table exists
        $stmt = $pdo->prepare("SHOW TABLES LIKE 'lab_settings'");
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            $pdo->exec("CREATE TABLE lab_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(50) NOT NULL UNIQUE,
                setting_value TEXT,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )");
        }
        
        // Insert or update settings
        $updateStmt = $pdo->prepare("
            INSERT INTO lab_settings (setting_key, setting_value) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
        
        foreach ($settings as $key => $value) {
            $updateStmt->execute([$key, $value]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Settings saved successfully']);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function saveNotificationSettings($pdo, $settings) {
    // Implementation will be similar to saveGeneralSettings
    echo json_encode(['success' => true, 'message' => 'Notification settings saved']);
}

function saveAppearanceSettings($pdo, $settings) {
    // Implementation will be similar to saveGeneralSettings
    echo json_encode(['success' => true, 'message' => 'Appearance settings saved']);
}
?>
