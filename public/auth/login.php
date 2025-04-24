<?php
require_once dirname(__DIR__) . '/config/config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
if ($_SESSION['login_attempts'] > 5) {
    redirect('/auth/login.php', 'Too many attempts. Try again later.', 'danger');
} */

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('/dashboard/dashboard.php');
}

// Check remember me cookie
if (checkRememberMe()) {
    redirect('/dashboard/dashboard.php');
}

$page_title = 'Login';
$active_page = 'login';
$extra_css = ['auth.css'];

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Validate input
    if (empty($email) || empty($password)) {
        redirect('/auth/login.php', 'Please fill in all fields', 'warning');
    }
    
    // Check credentials
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        
        // Set remember me cookie if requested
        if ($remember) {
            setRememberMe($user['id']);
        }
        
        // Update last login
        $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user['id']]);
        
        redirect('/dashboard/dashboard.php', 'Welcome back!', 'success');
    } else {
        redirect('/auth/login.php', 'Invalid email or password', 'danger');
    }
}

include_once '../templates/auth_header.php';
?>

<div class="row justify-content-center">
    <div class="col-xl-10 col-lg-12 col-md-9">
        <div class="card o-hidden border-0 shadow-lg">
            <div class="card-body p-0">
                <div class="row">
                    <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
                    <div class="col-lg-6">
                        <div class="p-5">
                            <div class="text-center mb-4">
                                <h1 class="h4 text-gray-900 mb-2">Welcome Back!</h1>
                                <p class="text-muted">Enter your email and password to continue</p>
                            </div>
                            
                            <?php include_once dirname(__DIR__) . '/includes/flash-messages.php'; ?>
                            
                            <form class="user" method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" class="form-control" name="email" required autofocus>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" name="password" required>
                                        <button class="btn btn-outline-secondary toggle-password" type="button">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                    <label class="form-check-label" for="remember">Remember Me</label>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login
                                </button>
                            </form>
                            
                            <hr>
                            
                            <div class="text-center">
                                <a href="<?php echo BASE_URL; ?>/auth/forgot-password.php" class="small">
                                    Forgot Password?
                                </a>
                            </div>
                            
                            <div class="text-center mt-3">
                                <p class="small mb-0">
                                    Don't have an account? 
                                    <a href="<?php echo BASE_URL; ?>/auth/register.php">Register here</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../templates/auth_footer.php'; ?>