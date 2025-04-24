<?php
// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Session configuration
session_start();

// Base URL configuration
define('BASE_URL', ''); // Kosongkan BASE_URL karena sudah ditangani oleh Laragon

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'address_api_provider');
define('DB_USER', 'root');
define('DB_PASS', '1234');

// Site configuration
define('SITE_NAME', 'API Provider');

// API configuration
define('API_VERSION', '1.0.0');
define('API_BASE_URL', '/api');
define('API_KEY_LENGTH', 32);

// Session timeout (in seconds)
define('SESSION_TIMEOUT', 3600); // 1 hour

// Upload configuration
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// Include required files
require_once __DIR__ . '/../includes/Database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/api_functions.php';

// Set timezone
date_default_timezone_set('Asia/Jakarta');

try {
    // Initialize database connection
    $db = Database::getInstance()->getConnection();
} catch (Exception $e) {
    error_log("Database Connection Error: " . $e->getMessage());
    die("Could not connect to the database. Please check your configuration.");
}

// Check session timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
    session_unset();
    session_destroy();
    redirect('/auth/login.php');
    exit;
}
$_SESSION['last_activity'] = time();

// Create uploads directory if it doesn't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0777, true);
}