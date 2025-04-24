<?php
/**
 * API related functions
 * This file contains functions specific to API operations
 */

/**
 * Validate API key and track usage
 */
function validateApiKey($api_key) {
    global $db;
    
    try {
        $query = "SELECT ak.id, ak.user_id, ak.status, 
                         rl.daily_limit, rl.monthly_limit,
                         (SELECT COUNT(*) FROM api_requests WHERE api_key_id = ak.id AND DATE(created_at) = CURDATE()) as daily_usage,
                         (SELECT COUNT(*) FROM api_requests WHERE api_key_id = ak.id AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())) as monthly_usage
                  FROM api_keys ak
                  LEFT JOIN rate_limits rl ON ak.id = rl.api_key_id
                  WHERE ak.api_key = :api_key";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':api_key', $api_key);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $key_data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Beri nilai default jika limit NULL
            $daily_limit = $key_data['daily_limit'] ?? 1000; // Sesuaikan dengan default
            $monthly_limit = $key_data['monthly_limit'] ?? 10000;
            
              // Cek status aktif
            if ($key_data['status'] !== 'active') {
                return [
                    'valid' => false,
                    'message' => 'API key is inactive'
                ];
            }

            // Check daily limit
            if ($key_data['daily_usage'] >= $daily_limit) {
                return [
                    'valid' => false,
                    'message' => 'Daily API limit exceeded'
                ];
            }
            
            // Check monthly limit
            if ($key_data['monthly_usage'] >= $monthly_limit) {
                return [
                    'valid' => false,
                    'message' => 'Monthly API limit exceeded'
                ];
            }
            
            return [
                'valid' => true,
                'api_key_id' => $key_data['id'],
                'user_id' => $key_data['user_id']
            ];
        }
        
        
        return [
            'valid' => false,
            'message' => 'Invalid API key'
        ];
    } catch (Exception $e) {
        error_log("API Key Validation Error: " . $e->getMessage());
        return [
            'valid' => false,
            'message' => 'Error validating API key'
        ];
    }
}

/**
 * Track API usage for a specific request
 * 
 * @param int $api_key_id The ID of the API key used
 * @param string $endpoint The API endpoint accessed
 * @param int $response_status HTTP response status code
 * @param float $start_time Microtime when the request started
 * @return bool True if tracking was successful, false otherwise
 */
function trackApiUsage($user_id, $api_key_id, $endpoint, $response_status, $start_time) {
    global $db;
    try {
        
        
        // Get user_id for the API key
        $stmt = $db->prepare("SELECT user_id FROM api_keys WHERE id = ?");
        $stmt->execute([$api_key_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            throw new Exception('Invalid API key ID');
        }
        
        $user_id = $result['user_id'];
        
        // Begin transaction
        $db->beginTransaction();
        
        // Insert request record
        $query = "INSERT INTO api_requests (user_id, api_key_id, endpoint, method, response_code, created_at) 
                  VALUES (?, ?, ?, 'GET', ?, NOW())";
        
        $stmt = $db->prepare($query);
        if (!$stmt->execute([$user_id, $api_key_id, $endpoint, $response_status])) {
            throw new Exception('Failed to track API usage');
        }
        
        // Update last used timestamp
        // Update last used in api_keys
        
        
        
        $update_query = "UPDATE api_keys SET last_used = NOW() WHERE id = ?";
        $update_stmt = $db->prepare($update_query);
        if (!$update_stmt->execute([$api_key_id])) {
            throw new Exception('Failed to update API key last used timestamp');
        }
        
        // Commit transaction
        $db->commit();
        return true;
        
    } catch (Exception $e) {
        // Rollback transaction on error
        if ($db->inTransaction()) {
            $db->rollBack();
            error_log("Database error in trackApiUsage: " . $e->getMessage());
        }
        
        error_log("API Usage Tracking Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get popular endpoints for a user
 * 
 * @param int $user_id The user ID to get popular endpoints for
 * @param int $limit Maximum number of endpoints to return (default: 5)
 * @return array Array of popular endpoints with their request counts
 * @throws Exception If database operation fails
 */
function getPopularEndpoints($user_id, $limit = 5) {
    try {
        global $db;
        
        // Validate inputs
        if (!is_numeric($user_id) || $user_id <= 0) {
            throw new Exception('Invalid user ID');
        }
        
        if (!is_numeric($limit) || $limit <= 0) {
            $limit = 5; // Default to 5 if invalid limit provided
        }
        
        $query = "SELECT 
                    ar.endpoint,
                    COUNT(*) as requests
                  FROM api_requests ar
                  JOIN api_keys ak ON ar.api_key_id = ak.id
                  WHERE ak.user_id = :user_id
                  AND ar.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                  GROUP BY ar.endpoint
                  ORDER BY requests DESC
                  LIMIT :limit";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error in getPopularEndpoints: " . $e->getMessage());
        return [];
    }
}

/**
 * Get API key details
 * 
 * @param int $api_key_id The ID of the API key
 * @return array|false API key details or false if not found
 */
function getApiKeyDetails($api_key_id) {
    try {
        global $db;
        
        if (!is_numeric($api_key_id) || $api_key_id <= 0) {
            throw new Exception('Invalid API key ID');
        }
        
        $query = "SELECT 
                    ak.*,
                    u.username,
                    u.email,
                    rl.daily_limit,
                    rl.monthly_limit,
                    (SELECT COUNT(*) FROM api_usage WHERE api_key_id = ak.id AND DATE(request_time) = CURDATE()) as daily_usage,
                    (SELECT COUNT(*) FROM api_usage WHERE api_key_id = ak.id AND MONTH(request_time) = MONTH(CURDATE()) AND YEAR(request_time) = YEAR(CURDATE())) as monthly_usage
                  FROM api_keys ak
                  JOIN users u ON ak.user_id = u.id
                  LEFT JOIN rate_limits rl ON ak.id = rl.api_key_id
                  WHERE ak.id = :api_key_id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':api_key_id', $api_key_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("API Key Details Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if the API key has exceeded its rate limit
 * @param string $api_key The API key to check
 * @return bool True if within limit, false if exceeded
 */
function checkRateLimit($api_key) {
    global $db;
    try {
        $query = "SELECT 
                    rl.daily_limit, 
                    rl.monthly_limit,
                    (SELECT COUNT(*) FROM api_requests WHERE api_key_id = ak.id AND DATE(created_at) = CURDATE()) as daily_usage,
                    (SELECT COUNT(*) FROM api_requests WHERE api_key_id = ak.id AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())) as monthly_usage
                  FROM api_keys ak
                  LEFT JOIN rate_limits rl ON ak.id = rl.api_key_id
                  WHERE ak.api_key = :api_key";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':api_key', $api_key);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Set default limits if NULL
            $daily_limit = $data['daily_limit'] ?? 1000;
            $monthly_limit = $data['monthly_limit'] ?? 10000;
            
            // Check daily limit
            if ($data['daily_usage'] >= $daily_limit) {
                return false;
            }
            
            // Check monthly limit
            if ($data['monthly_usage'] >= $monthly_limit) {
                return false;
            }
            
            return true;
        }
        
        // API key not found
        return false;
    } catch (Exception $e) {
        error_log("Rate Limit Check Error: " . $e->getMessage());
        return false;
    }
}

function logResponseTime($user_id, $path, $response_time) {
    $log = "User: $user_id, Path: $path, Response Time: $response_time";
    file_put_contents('response_time.log', $log . PHP_EOL, FILE_APPEND);
}

function updateApiUsage($user_id, $path, $response_time) {
    // Misalnya, update statistik di database
    $log = "Updated usage for User: $user_id, Path: $path, Time: $response_time";
    file_put_contents('usage_stats.log', $log . PHP_EOL, FILE_APPEND);
}

?>

