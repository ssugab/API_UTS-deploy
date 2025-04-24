<?php
require_once dirname(__DIR__) . '/config/config.php';

// Set response headers
header('Content-Type: application/json');

// Check API key
$api_key = $_SERVER['HTTP_X_API_KEY'] ?? null;
if (!$api_key || !validateApiKey($api_key)) {
    http_response_code(401);
    echo json_encode([
        'status' => 401,
        'message' => 'Invalid API key',
        'errors' => ['api_key' => ['API key is required']]
    ]);
    exit;
}

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = str_replace('/api/address/', '', $path);

// Handle different endpoints
switch ($path) {
    case 'provinces':
        if ($method === 'GET') {
            getProvinces();
        } else {
            methodNotAllowed();
        }
        break;
        
    case 'cities':
        if ($method === 'GET') {
            getCities();
        } else {
            methodNotAllowed();
        }
        break;
        
    case 'subdistricts':
        if ($method === 'GET') {
            getSubdistricts();
        } else {
            methodNotAllowed();
        }
        break;
        
    case 'villages':
        if ($method === 'GET') {
            getVillages();
        } else {
            methodNotAllowed();
        }
        break;
        
    case 'postal-codes':
        if ($method === 'GET') {
            getPostalCodes();
        } else {
            methodNotAllowed();
        }
        break;
        
    case 'search':
        if ($method === 'GET') {
            searchAddress();
        } else {
            methodNotAllowed();
        }
        break;
        
    default:
        notFound();
        break;
}

function getProvinces() {
    try {
        $response = file_get_contents('https://alamat.thecloudalert.com/api/provinsi/get/');
        $data = json_decode($response, true);
        
        if ($data['status'] === 200) {
            echo json_encode([
                'status' => 200,
                'message' => 'Success',
                'data' => $data['result']
            ]);
        } else {
            throw new Exception($data['message']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 500,
            'message' => 'Failed to fetch provinces',
            'errors' => ['server' => [$e->getMessage()]]
        ]);
    }
}

function getCities() {
    $province_id = $_GET['province_id'] ?? null;
    
    if (!$province_id) {
        http_response_code(400);
        echo json_encode([
            'status' => 400,
            'message' => 'Province ID is required',
            'errors' => ['province_id' => ['Province ID is required']]
        ]);
        return;
    }
    
    try {
        $response = file_get_contents("https://alamat.thecloudalert.com/api/kabkota/get/?d_provinsi_id={$province_id}");
        $data = json_decode($response, true);
        
        if ($data['status'] === 200) {
            echo json_encode([
                'status' => 200,
                'message' => 'Success',
                'data' => $data['result']
            ]);
        } else {
            throw new Exception($data['message']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 500,
            'message' => 'Failed to fetch cities',
            'errors' => ['server' => [$e->getMessage()]]
        ]);
    }
}

function getSubdistricts() {
    $city_id = $_GET['city_id'] ?? null;
    
    if (!$city_id) {
        http_response_code(400);
        echo json_encode([
            'status' => 400,
            'message' => 'City ID is required',
            'errors' => ['city_id' => ['City ID is required']]
        ]);
        return;
    }
    
    try {
        $response = file_get_contents("https://alamat.thecloudalert.com/api/kecamatan/get/?d_kabkota_id={$city_id}");
        $data = json_decode($response, true);
        
        if ($data['status'] === 200) {
            echo json_encode([
                'status' => 200,
                'message' => 'Success',
                'data' => $data['result']
            ]);
        } else {
            throw new Exception($data['message']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 500,
            'message' => 'Failed to fetch subdistricts',
            'errors' => ['server' => [$e->getMessage()]]
        ]);
    }
}

function getVillages() {
    $subdistrict_id = $_GET['subdistrict_id'] ?? null;
    
    if (!$subdistrict_id) {
        http_response_code(400);
        echo json_encode([
            'status' => 400,
            'message' => 'Subdistrict ID is required',
            'errors' => ['subdistrict_id' => ['Subdistrict ID is required']]
        ]);
        return;
    }
    
    try {
        $response = file_get_contents("https://alamat.thecloudalert.com/api/kelurahan/get/?d_kecamatan_id={$subdistrict_id}");
        $data = json_decode($response, true);
        
        if ($data['status'] === 200) {
            echo json_encode([
                'status' => 200,
                'message' => 'Success',
                'data' => $data['result']
            ]);
        } else {
            throw new Exception($data['message']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 500,
            'message' => 'Failed to fetch villages',
            'errors' => ['server' => [$e->getMessage()]]
        ]);
    }
}

function getPostalCodes() {
    $city_id = $_GET['city_id'] ?? null;
    $subdistrict_id = $_GET['subdistrict_id'] ?? null;
    
    if (!$city_id || !$subdistrict_id) {
        http_response_code(400);
        echo json_encode([
            'status' => 400,
            'message' => 'City ID and Subdistrict ID are required',
            'errors' => [
                'city_id' => ['City ID is required'],
                'subdistrict_id' => ['Subdistrict ID is required']
            ]
        ]);
        return;
    }
    
    try {
        $response = file_get_contents("https://alamat.thecloudalert.com/api/kodepos/get/?d_kabkota_id={$city_id}&d_kecamatan_id={$subdistrict_id}");
        $data = json_decode($response, true);
        
        if ($data['status'] === 200) {
            echo json_encode([
                'status' => 200,
                'message' => 'Success',
                'data' => $data['result']
            ]);
        } else {
            throw new Exception($data['message']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 500,
            'message' => 'Failed to fetch postal codes',
            'errors' => ['server' => [$e->getMessage()]]
        ]);
    }
}

function searchAddress() {
    $keyword = $_GET['keyword'] ?? null;
    
    if (!$keyword) {
        http_response_code(400);
        echo json_encode([
            'status' => 400,
            'message' => 'Keyword is required',
            'errors' => ['keyword' => ['Keyword is required']]
        ]);
        return;
    }
    
    try {
        $response = file_get_contents("https://alamat.thecloudalert.com/api/cari/index/?keyword=" . urlencode($keyword));
        $data = json_decode($response, true);
        
        if ($data['status'] === 200) {
            echo json_encode([
                'status' => 200,
                'message' => 'Success',
                'data' => $data['result']
            ]);
        } else {
            throw new Exception($data['message']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 500,
            'message' => 'Failed to search address',
            'errors' => ['server' => [$e->getMessage()]]
        ]);
    }
}

function methodNotAllowed() {
    http_response_code(405);
    echo json_encode([
        'status' => 405,
        'message' => 'Method not allowed',
        'errors' => ['method' => ['Method not allowed']]
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