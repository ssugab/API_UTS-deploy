<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/api_functions.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set response headers
header('Content-Type: application/json');

try {
    // Check API key
    $api_key = $_SERVER['HTTP_X_API_KEY'] ?? null;
    $validation_result = validateApiKey($api_key);

    if (!$api_key || !$validation_result['valid']) {
        http_response_code(401);
        echo json_encode([
            'status' => 401,
            'message' => $validation_result['message'] ?? 'Invalid API key',
            'errors' => [
                'api_key' => [$validation_result['message'] ?? 'API key is required']
            ]
        ]);
        exit;
    }

    $user_id = $validation_result['user_id'];
    $api_key_id = $validation_result['api_key_id'];

    // Get request method and path
    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path = str_replace('/uts_api2/api/', '', $path);

    // Log API request
    logApiRequest($user_id, $api_key_id, $path, $method);

    // Check rate limit
    if (!checkRateLimit($api_key)) {
        http_response_code(429);
        echo json_encode([
            'status' => 429,
            'message' => 'Rate limit exceeded',
            'errors' => ['rate_limit' => ['Too many requests']]
        ]);
        exit;
    }

    // Map our endpoints to third-party API endpoints
    $endpoint_map = [
        'address/provinces' => 'https://alamat.thecloudalert.com/api/provinsi/get/',
        'address/cities' => 'https://alamat.thecloudalert.com/api/kabkota/get/',
        'address/subdistricts' => 'https://alamat.thecloudalert.com/api/kecamatan/get/',
        'address/villages' => 'https://alamat.thecloudalert.com/api/kelurahan/get/',
        'address/postal-codes' => 'https://alamat.thecloudalert.com/api/kodepos/get/',
        'address/search' => 'https://alamat.thecloudalert.com/api/cari/index/'
    ];

    // Get target URL
    $target_url = $endpoint_map[$path] ?? null;
    if (!$target_url) {
        notFound();
        exit;
    }

    // Build query string
    $query_string = http_build_query($_GET);
    if ($query_string) {
        $target_url .= '?' . $query_string;
    }

    // Make request to third-party API
    $start_time = microtime(true);
    
    $context = stream_context_create([
        'http' => [
            'method' => $method,
            'header' => [
                'User-Agent: PHP',
                'Accept: application/json'
            ],
            'timeout' => 30
        ]
    ]);
    
    $response = file_get_contents($target_url, false, $context);
    if ($response === false) {
        throw new Exception('Failed to fetch data from third-party API');
    }
    
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON response from third-party API');
    }
    
    $response_time = microtime(true) - $start_time;
    
    // Ambil response status
    $response_status = $http_response_header[0] ?? '200 OK';
    preg_match('/\d{3}/', $response_status, $matches);
    $response_status = (int)($matches[0] ?? 200);

    // Track usage setelah request berhasil
    trackApiUsage($user_id, $validation_result['api_key_id'], $path, $response_status, $start_time);

    // Log response time
    logResponseTime($user_id, $path, $response_time); 
    
    // Update usage statistics
    updateApiUsage($user_id, $path, $response_time); 
    
    // Return response
    echo json_encode([
        'status' => $data['status'] ?? 200,
        'message' => $data['message'] ?? 'Success',
        'data' => $data['result'] ?? $data['data'] ?? []
    ]);

} catch (Exception $e) {
    error_log('API Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 500,
        'message' => 'Internal Server Error',
        'errors' => ['server' => [$e->getMessage()]]
    ]);
}

function notFound() {
    http_response_code(404);
    echo json_encode([
        'status' => 404,
        'message' => 'Endpoint not found',
        'errors' => ['endpoint' => ['Endpoint not found']]
    ]);
} 