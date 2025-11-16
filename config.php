<?php
// config.php - Konfigurasi Sistem

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'gizi_db');

// Application Configuration
define('APP_NAME', 'Sistem Diagnosa Status Gizi Anak');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/project_gizi/');

// Path Configuration
define('UPLOAD_PATH', __DIR__ . '/uploads/');
define('EXPORT_PATH', __DIR__ . '/exports/');
define('LOG_PATH', __DIR__ . '/logs/');
define('MODEL_PATH', __DIR__ . '/model/');

// Python Configuration
define('PYTHON_PATH', 'python'); // atau 'python3' di Linux/Mac
define('PYTHON_SCRIPT_PREDICT', MODEL_PATH . 'predict_gizi.py');

// Session Configuration
define('SESSION_TIMEOUT', 3600); // 1 jam

// Security
define('ENABLE_CSRF', true);
define('MAX_LOGIN_ATTEMPTS', 5);

// File Upload
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_EXTENSIONS', ['csv', 'xlsx']);

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Error Reporting (development mode)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create directories if not exist
$dirs = [UPLOAD_PATH, EXPORT_PATH, LOG_PATH];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}
?>