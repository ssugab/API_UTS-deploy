<?php
// General utility functions

/**
 * Sanitize user input
 */
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

/**
 * Generate random string for tokens and API keys
 */
function generateRandomString($length = 32) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    
    return $randomString;
}

/**
 * Generate a new API key
 */
function generateApiKey() {
    return bin2hex(random_bytes(16)); // 32 character hex string
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Redirect with flash message
 */
function redirect($url, $message = null, $type = 'info') {
    if ($message) {
        $_SESSION['flash'] = [
            'message' => $message,
            'type' => $type
        ];
    }
    // Log pengalihan
    error_log("Redirecting to: $url from " . $_SERVER['PHP_SELF']);

    // Jika URL dimulai dengan /, tambahkan BASE_URL
    if (strpos($url, '/') === 0) {
        $url = $url; // Tidak perlu menambahkan BASE_URL karena sudah ditangani oleh Laragon
    }
    
    header("Location: $url");
    exit;
}

/**
 * Display flash message if exists then clear it
 */
function displayFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        $type = $flash['type'];
        $message = $flash['message'];
        
        echo "<div class='alert alert-$type'>$message</div>";
        
        unset($_SESSION['flash']);
    }
}

/**
 * Get user data by ID
 */
function getUserById($id) {
    global $db;
    
    $query = "SELECT id, username, email, full_name, created_at, last_login, status 
              FROM users WHERE id = :id";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Format date for display
 * @param string $date Date string
 * @param string $format Format string (optional)
 * @return string Formatted date
 */
function formatDate($date, $format = 'd M Y H:i') {
    return date($format, strtotime($date));
}

/**
 * Validate email format
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Check if a string is a valid username (alphanumeric and underscore only)
 */
function isValidUsername($username) {
    return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
}

/**
 * Check if password meets complexity requirements
 */
function isStrongPassword($password) {
    // At least 8 characters, contains uppercase, lowercase and numbers
    return (strlen($password) >= 8 &&
            preg_match('/[A-Z]/', $password) &&
            preg_match('/[a-z]/', $password) &&
            preg_match('/[0-9]/', $password));
}

/**
 * Register a new user
 * @param string $username Username
 * @param string $email Email address
 * @param string $password Password
 * @param string $full_name Full name
 * @return array Result with success status and message
 */
function registerUser($username, $email, $password, $full_name) {
    global $db;
    
    try {
        // Validate inputs
        if (!isValidUsername($username)) {
            return [
                'success' => false,
                'message' => 'Username harus terdiri dari 3-20 karakter alfanumerik atau underscore'
            ];
        }
        
        if (!isValidEmail($email)) {
            return [
                'success' => false,
                'message' => 'Format email tidak valid'
            ];
        }
        
        if (!isStrongPassword($password)) {
            return [
                'success' => false,
                'message' => 'Password harus minimal 8 karakter dan mengandung huruf besar, huruf kecil, dan angka'
            ];
        }
        
        // Check if username or email already exists
        $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetchColumn() > 0) {
            return [
                'success' => false,
                'message' => 'Username atau email sudah terdaftar'
            ];
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Begin transaction
        $db->beginTransaction();
        
        // Insert user
        $stmt = $db->prepare("INSERT INTO users (username, email, password, full_name, created_at) 
                             VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$username, $email, $hashed_password, $full_name]);
        $user_id = $db->lastInsertId();
        
        // Create default API key
        $api_key = generateApiKey();
        $stmt = $db->prepare("INSERT INTO api_keys (user_id, api_key, name, status, created_at) 
                             VALUES (?, ?, 'Default Key', 'active', NOW())");
        $stmt->execute([$user_id, $api_key]);
        
        // Set default rate limits
        $stmt = $db->prepare("INSERT INTO rate_limits (api_key_id, daily_limit, monthly_limit) 
                             VALUES (?, 1000, 10000)");
        $stmt->execute([$db->lastInsertId()]);
        
        // Commit transaction
        $db->commit();
        
        return [
            'success' => true,
            'message' => 'Registrasi berhasil! Silakan login dengan akun Anda.'
        ];
        
    } catch (Exception $e) {
        // Rollback transaction on error
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        
        error_log("Registration Error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Terjadi kesalahan saat registrasi. Silakan coba lagi.'
        ];
    }
}

/**
 * Logout user and clear session
 * @param int $user_id User ID to logout
 */
function logoutUser($user_id = null) {
    // Clear remember me cookie if exists
    if ($user_id) {
        clearRememberMe($user_id);
    }
    
    // Clear all session data
    $_SESSION = array();
    
    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    
    // Destroy the session
    session_destroy();
}

/**
 * Handle API Error Response
 */
function apiError($message, $status = 400) {
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'result' => null
    ]);
    exit;
}

/**
 * Handle API Success Response
 */
function apiSuccess($data, $message = 'Success', $status = 200) {
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'result' => $data
    ]);
    exit;
}

/**
 * Get user avatar URL
 * @param int $user_id User ID
 * @return string Avatar URL
 */
function getUserAvatar($user_id) {
    // Check if user has custom avatar
    $avatar_path = "assets/img/avatars/{$user_id}.jpg";
    if (file_exists($avatar_path)) {
        return '/' . $avatar_path;
    }
    
    // Return default avatar
    return '/assets/img/default-avatar.png';
}

/**
 * Format number to readable format
 * @param int $number Number to format
 * @return string Formatted number
 */
function formatNumber($number) {
    if ($number >= 1000000) {
        return round($number / 1000000, 1) . 'M';
    } elseif ($number >= 1000) {
        return round($number / 1000, 1) . 'K';
    }
    return number_format($number);
}

/**
 * Validate API key format
 * @param string $api_key API key to validate
 * @return bool True if valid, false otherwise
 */
function isValidApiKey($api_key) {
    return preg_match('/^[a-zA-Z0-9]{32}$/', $api_key) === 1;
}

/**
 * Get user's current plan details
 * @param int $user_id User ID
 * @return array Plan details
 */
function getUserPlan($user_id) {
    global $db;
    
    $stmt = $db->prepare("SELECT p.* FROM plans p 
                         JOIN user_subscriptions us ON p.id = us.plan_id 
                         WHERE us.user_id = ? AND us.status = 'active'
                         ORDER BY us.created_at DESC LIMIT 1");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Check if user has reached their API request limit
 * @param int $user_id User ID
 * @return bool True if limit reached, false otherwise
 */
function hasReachedRequestLimit($user_id) {
    $plan = getUserPlan($user_id);
    if (!$plan) return true;
    
    global $db;
    $stmt = $db->prepare("SELECT COUNT(*) as request_count 
                         FROM api_requests 
                         WHERE user_id = ? 
                         AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result['request_count'] >= $plan['monthly_request_limit'];
}

/**
 * Log API request
 * @param int $user_id User ID
 * @param string $endpoint Endpoint accessed
 * @param string $method HTTP method
 * @param int $response_code HTTP response code
 * @return bool True if logged successfully
 */
function logApiRequest($user_id, $api_key_id, $endpoint, $method = 'GET', $response_code = 200) {
    global $db;
    
    $stmt = $db->prepare("INSERT INTO api_requests (user_id, api_key_id, endpoint, method, response_code) 
                         VALUES (?, ?, ?, ?, ?)"); // ✅ Sertakan api_key_id
    return $stmt->execute([$user_id, $api_key_id, $endpoint, $method, $response_code]);
}   

/**
 * Get user's API usage statistics
 * @param int $user_id User ID
 * @param string $period Period (daily/weekly/monthly)
 * @return array Usage statistics
 */
function getUserApiUsageStats($user_id, $period = 'daily') {
    global $db;
    
    $interval = '1 DAY';
    $format = '%Y-%m-%d';
    
    switch ($period) {
        case 'weekly':
            $interval = '1 WEEK';
            $format = '%Y-%u';
            break;
        case 'monthly':
            $interval = '1 MONTH';
            $format = '%Y-%m';
            break;
    }
    
    $stmt = $db->prepare("SELECT 
                            DATE_FORMAT(created_at, ?) as date,
                            COUNT(*) as requests,
                            COUNT(DISTINCT endpoint) as unique_endpoints
                         FROM api_requests 
                         WHERE user_id = ? 
                         AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                         GROUP BY date
                         ORDER BY date ASC");
    
    $stmt->execute([$format, $user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get user's API keys
 * @param int $user_id User ID
 * @return array Array of API keys
 */
function getUserApiKeys($user_id) {
    global $db;
    
    $stmt = $db->prepare("SELECT * FROM api_keys WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Create new API key for user
 * @param int $user_id User ID
 * @param string $name Key name
 * @return bool|string API key if successful, false otherwise
 */
function createApiKey($user_id, $name) {
    global $db;
    
    // Generate API key
    $api_key = generateRandomString(32);
    
    // Insert ke api_keys
    $stmt = $db->prepare("INSERT INTO api_keys (user_id, api_key, name) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $api_key, $name]);
    $api_key_id = $db->lastInsertId();
    
    // Tambahkan entri default ke rate_limits
    $stmt = $db->prepare("INSERT INTO rate_limits (api_key_id, daily_limit, monthly_limit) 
                         VALUES (?, 1000, 10000)"); // Sesuaikan nilai default
    $stmt->execute([$api_key_id]);
    
    return $api_key;
}

/**
 * Validate API key and get user ID
 * @param string $api_key API key
 * @return int|false User ID if valid, false otherwise
 */
function validateApiKeySimple($api_key) {
    if (!isValidApiKey($api_key)) return false;
    
    global $db;
    $stmt = $db->prepare("SELECT user_id FROM api_keys WHERE api_key = ? AND status = 'active'");
    $stmt->execute([$api_key]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result ? $result['user_id'] : false;
}

/**
 * Check if remember me cookie exists and is valid
 * @return bool True if valid, false otherwise
 */
function checkRememberMe() {
    if (isset($_COOKIE['remember_token'])) {
        global $db;
        $token = $_COOKIE['remember_token'];
        
        $stmt = $db->prepare("SELECT user_id FROM remember_tokens WHERE token = ? AND expires_at > NOW()");
        $stmt->execute([$token]);
        $result = $stmt->fetch();
        
        if ($result) {
            $_SESSION['user_id'] = $result['user_id'];
            return true;
        }
        
        // Token expired or invalid, remove cookie
        setcookie('remember_token', '', time() - 3600, '/');
    }
    return false;
}

/**
 * Set remember me cookie
 * @param int $user_id User ID
 * @return bool True if successful, false otherwise
 */
function setRememberMe($user_id) {
    global $db;
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
    
    $stmt = $db->prepare("INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
    if ($stmt->execute([$user_id, $token, $expires])) {
        setcookie('remember_token', $token, strtotime('+30 days'), '/', '', true, true);
        return true;
    }
    return false;
}

/**
 * Clear remember me cookie
 * @param int $user_id User ID
 */
function clearRememberMe($user_id) {
    global $db;
    $stmt = $db->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
    }
}

/**
 * Check if user is an administrator
 * @return bool True if user is admin, false otherwise
 */
function isAdmin() {
    if (!isLoggedIn()) return false;
    
    global $db;
    $stmt = $db->prepare("SELECT is_admin FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $result && $result['is_admin'] == 1;
}


function updateUserProfile($user_id, $full_name, $email) {
    global $db; // Asumsi Anda menggunakan koneksi database PDO melalui variabel global $db
    try {
        $stmt = $db->prepare("UPDATE users SET full_name = :full_name, email = :email WHERE id = :user_id");
        $stmt->execute([
            'full_name' => $full_name,
            'email' => $email,
            'user_id' => $user_id
        ]);
        return $stmt->rowCount() > 0; // Mengembalikan true jika ada baris yang diupdate
    } catch (PDOException $e) {
        error_log("Error updating profile: " . $e->getMessage());
        return false; // Mengembalikan false jika terjadi error
    }
}

/**
 * Change the user's password
 * @param int $user_id The ID of the user
 * @param string $current_password The current password entered by the user
 * @param string $new_password The new password to set
 * @return bool True if the password was changed successfully, false otherwise
 */
function changeUserPassword($user_id, $current_password, $new_password) {
    global $db; // Asumsi koneksi database PDO tersedia melalui variabel global $db

    try {
        // Ambil kata sandi saat ini dari database
        $stmt = $db->prepare("SELECT password FROM users WHERE id = :user_id");
        $stmt->execute(['user_id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            error_log("User not found for ID: $user_id");
            return false;
        }

        // Verifikasi kata sandi saat ini
        if (!password_verify($current_password, $user['password'])) {
            error_log("Current password is incorrect for user ID: $user_id");
            return false;
        }

        // Enkripsi kata sandi baru
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Perbarui kata sandi di database
        $stmt = $db->prepare("UPDATE users SET password = :password WHERE id = :user_id");
        $stmt->execute([
            'password' => $hashed_password,
            'user_id' => $user_id
        ]);

        // Periksa apakah pembaruan berhasil
        if ($stmt->rowCount() > 0) {
            return true;
        } else {
            error_log("Failed to update password for user ID: $user_id");
            return false;
        }
    } catch (PDOException $e) {
        error_log("Database error in changeUserPassword: " . $e->getMessage());
        return false;
    }
}

function getUserUsageStats($user_id) {
    global $db; // Asumsi $db adalah koneksi PDO dari config.php
    
    // Inisialisasi array untuk menyimpan statistik
    $stats = [
        'total_requests' => 0,
        'successful_requests' => 0,
        'failed_requests' => 0,
        'recent_activity' => [],
        'endpoint_usage' => []
    ];
    
    try {
        // Query untuk total requests
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM api_requests WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $stats['total_requests'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Query untuk successful requests (status 200-299)
        $stmt = $db->prepare("SELECT COUNT(*) as success FROM api_requests WHERE user_id = ? AND response_code >= 200 AND response_code < 300");
        $stmt->execute([$user_id]);
        $stats['successful_requests'] = $stmt->fetch(PDO::FETCH_ASSOC)['success'];
        
        // Hitung failed requests
        $stats['failed_requests'] = $stats['total_requests'] - $stats['successful_requests'];
        
        // Query untuk recent activity (10 permintaan terakhir)
        $stmt = $db->prepare("SELECT endpoint, response_code as status, response_time, created_at as timestamp 
                              FROM api_requests 
                              WHERE user_id = ? 
                              ORDER BY created_at DESC 
                              LIMIT 10");
        $stmt->execute([$user_id]);
        $stats['recent_activity'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Query untuk endpoint usage
        $stmt = $db->prepare("SELECT endpoint, 
                                     COUNT(*) as total_requests, 
                                     AVG(response_time) as avg_response_time,
                                     (SUM(CASE WHEN response_code >= 200 AND response_code < 300 THEN 1 ELSE 0 END) / COUNT(*)) * 100 as success_rate
                              FROM api_requests 
                              WHERE user_id = ? 
                              GROUP BY endpoint");
        $stmt->execute([$user_id]);
        $stats['endpoint_usage'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        // Catat error ke log tanpa menampilkan ke pengguna
        error_log("Error in getUserUsageStats: " . $e->getMessage());
    }
    
    return $stats;
}

?>