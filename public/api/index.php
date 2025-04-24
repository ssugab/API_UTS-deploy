<?php
// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/config.php';

// Set response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    apiError('Method not allowed', 405);
}

// Get API key from header
$headers = getallheaders();
$api_key = null;

if (isset($headers['Authorization'])) {
    $auth_header = $headers['Authorization'];
    if (preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
        $api_key = $matches[1];
    }
}

if (!$api_key) {
    apiError('API key is required', 401);
}

// Validate API key
$validation = validateApiKey($api_key);
if (!$validation['valid']) {
    apiError($validation['message'], 401);
}

// Parse request path
$request_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base_path = parse_url(API_BASE_URL, PHP_URL_PATH);
$endpoint = substr($request_path, strlen($base_path));
$endpoint = trim($endpoint, '/');

// Handle different endpoints
try {
    switch ($endpoint) {
        case 'provinces':
            // Get all provinces
            $query = "SELECT id, name, code FROM provinces ORDER BY name";
            $stmt = $db->query($query);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            apiSuccess($data);
            break;

        case (preg_match('/^provinces\/(\d+)\/cities$/', $endpoint, $matches) ? true : false):
            // Get cities by province ID
            $province_id = $matches[1];
            $query = "SELECT id, name, code, type, postal_code FROM cities WHERE province_id = ? ORDER BY name";
            $stmt = $db->prepare($query);
            $stmt->execute([$province_id]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            apiSuccess($data);
            break;

        case (preg_match('/^cities\/(\d+)\/districts$/', $endpoint, $matches) ? true : false):
            // Get districts by city ID
            $city_id = $matches[1];
            $query = "SELECT id, name, code FROM districts WHERE city_id = ? ORDER BY name";
            $stmt = $db->prepare($query);
            $stmt->execute([$city_id]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            apiSuccess($data);
            break;

        case (preg_match('/^districts\/(\d+)\/villages$/', $endpoint, $matches) ? true : false):
            // Get villages by district ID
            $district_id = $matches[1];
            $query = "SELECT id, name, code, postal_code FROM villages WHERE district_id = ? ORDER BY name";
            $stmt = $db->prepare($query);
            $stmt->execute([$district_id]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            apiSuccess($data);
            break;

        case 'search':
            // Search across all address levels
            if (!isset($_GET['q'])) {
                apiError('Search query is required', 400);
            }
            $search = '%' . $_GET['q'] . '%';
            
            $query = "SELECT 'province' as type, p.id, p.name, p.code, NULL as parent_id 
                     FROM provinces p WHERE p.name LIKE ?
                     UNION ALL
                     SELECT 'city' as type, c.id, c.name, c.code, c.province_id as parent_id 
                     FROM cities c WHERE c.name LIKE ?
                     UNION ALL
                     SELECT 'district' as type, d.id, d.name, d.code, d.city_id as parent_id 
                     FROM districts d WHERE d.name LIKE ?
                     UNION ALL
                     SELECT 'village' as type, v.id, v.name, v.code, v.district_id as parent_id 
                     FROM villages v WHERE v.name LIKE ?
                     ORDER BY type, name
                     LIMIT 100";
            
            $stmt = $db->prepare($query);
            $stmt->execute([$search, $search, $search, $search]);
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            apiSuccess($data);
            break;

        default:
            apiError('Endpoint not found', 404);
    }

    // Log successful API request
    trackApiUsage($validation['api_key_id'], $endpoint, 200, $_SERVER['REQUEST_TIME_FLOAT']);

} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage());
    apiError('Internal server error', 500);
} 