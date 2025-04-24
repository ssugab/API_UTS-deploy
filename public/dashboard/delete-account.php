<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('/auth/login.php', 'Please login to access the dashboard.', 'warning');
}

// Get user data
$user = getUserById($_SESSION['user_id']);
if (!$user) {
    redirect('/auth/login.php', 'User not found.', 'error');
}

// Generate and validate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle account deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        redirect('/dashboard/settings.php', 'Invalid CSRF token.', 'error');
    }

    try {
        // Start transaction
        $db->beginTransaction();
        
        // Delete user's API keys
        $stmt = $db->prepare("DELETE FROM api_keys WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        
        // Delete user's usage history
        $stmt = $db->prepare("DELETE FROM api_requests WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        
        // Delete user's account
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        
        // Commit transaction
        $db->commit();
        
        // Logout user completely
        logoutUser($_SESSION['user_id']);
        
        // Redirect to login page with success message
        redirect('/auth/login.php', 'Your account has been successfully deleted.', 'success');
    } catch (Exception $e) {
        // Rollback transaction on error
        $db->rollBack();
        error_log("Error deleting account for user ID {$_SESSION['user_id']}: " . $e->getMessage());
        redirect('/dashboard/settings.php', 'Failed to delete account. Please try again.', 'error');
    }
}

// If not POST request, redirect to settings page
// redirect('/dashboard/settings.php', 'Invalid request method.', 'error');