<?php
require_once dirname(__DIR__) . '/config/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('/auth/login.php', 'Please login to access the dashboard.', 'warning');
}

// Get user data
$user = getUserById($_SESSION['user_id']);
if (!$user) {
    redirect('/auth/login.php', 'User not found.', 'error');
} 

// Initialize variables with default values
$api_keys = [];
$usage_stats = [];
$popular_endpoints = [];

try {
    // Get user's API keys
    $api_keys = getUserApiKeys($_SESSION['user_id']);

    // Get API usage statistics
    $usage_stats = getUserApiUsageStats($_SESSION['user_id'], 'daily');

    // Get popular endpoints
    $popular_endpoints = getPopularEndpoints($_SESSION['user_id']);

    // Prepare chart data
    $chart_labels = [];
    $chart_data = [];
    foreach ($usage_stats as $stat) {
        $chart_labels[] = date('M d', strtotime($stat['date']));
        $chart_data[] = $stat['requests'];
    }
} catch (PDOException $e) {
    // Log the error but don't show it to the user
    error_log("Dashboard Error: " . $e->getMessage());
}

// Page title and active sidebar
$page_title = "Dashboard";
$active_sidebar = "dashboard";

// include_once '../templates/dashboard/header.php';
include_once '../templates/header.php';

?>

<div class="row">
    <!-- API Keys Card -->
    <div class="col-md-4 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="feature-icon bg-primary text-white me-3">
                    <i class="fas fa-key"></i>
                </div>
                <div>
                    <h5 class="card-title mb-0">API Keys</h5>
                    <h3 class="mt-2 mb-0"><?php echo count($api_keys); ?></h3>
                    <p class="text-muted small mb-0">Total API Keys</p>
                </div>
            </div>
            <div class="card-footer bg-white border-0 pt-0">
                <a href="<?php echo BASE_URL; ?>/dashboard/api-keys.php" class="btn btn-sm btn-outline-primary">
                    Manage API Keys <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Requests Card -->
    <div class="col-md-4 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="feature-icon bg-success text-white me-3">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div>
                    <h5 class="card-title mb-0">Requests</h5>
                    <h3 class="mt-2 mb-0">
                        <?php 
                        $total_requests = 0;
                        foreach ($usage_stats as $stat) {
                            $total_requests += $stat['requests'];
                        }
                        echo number_format($total_requests);
                        ?>
                    </h3>
                    <p class="text-muted small mb-0">Last 30 Days</p>
                </div>
            </div>
            <div class="card-footer bg-white border-0 pt-0">
                <a href="<?php echo BASE_URL; ?>/dashboard/usage.php" class="btn btn-sm btn-outline-success">
                    View Statistics <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Account Card -->
    <div class="col-md-4 mb-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body d-flex align-items-center">
                <div class="feature-icon bg-info text-white me-3">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <h5 class="card-title mb-0">Account</h5>
                    <h3 class="mt-2 mb-0"><?php echo htmlspecialchars($user['username']); ?></h3>
                    <p class="text-muted small mb-0">
                        Joined: <?php echo date('d M Y', strtotime($user['created_at'])); ?>
                    </p>
                </div>
            </div>
            <div class="card-footer bg-white border-0 pt-0">
                <a href="<?php echo BASE_URL; ?>/dashboard/profile.php" class="btn btn-sm btn-outline-info">
                    Edit Profile <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Welcome Message for New Users -->
<?php if (count($api_keys) === 0): ?>
    <div class="alert alert-info d-flex align-items-center" role="alert">
        <i class="fas fa-info-circle me-3 fs-4"></i>
        <div>
            <h5>Welcome to the API Provider Dashboard!</h5>
            <p class="mb-0">You don't have any API Keys yet. An API Key is required to access the API.</p>
            <a href="<?php echo BASE_URL; ?>/dashboard/api-keys.php" class="btn btn-sm btn-primary mt-2">
                <i class="fas fa-plus-circle me-1"></i> Create API Key Now
            </a>
        </div>
    </div>
<?php endif; ?>

<!-- Usage Chart and Popular Endpoints -->
<div class="row mt-4">
    <div class="col-md-8 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-chart-area me-2"></i> API Usage Last 30 Days</h5>
            </div>
            <div class="card-body">
                <canvas id="usageChart" height="250"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-route me-2"></i> Popular Endpoints</h5>
            </div>
            <div class="card-body">
                <?php if (count($popular_endpoints) > 0): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($popular_endpoints as $index => $endpoint): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge bg-primary rounded-pill me-2"><?php echo $index + 1; ?></span>
                                    <?php echo htmlspecialchars($endpoint['endpoint']); ?>
                                </div>
                                <span class="badge bg-light text-dark">
                                    <?php echo number_format($endpoint['requests']); ?> requests
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="text-center py-4">
                        <div class="mb-3 text-muted">
                            <i class="fas fa-chart-pie fa-3x"></i>
                        </div>
                        <p>No API usage data yet</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent API Keys Table -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-key me-2"></i> Recent API Keys</h5>
    </div>
    <div class="card-body">
        <?php if (count($api_keys) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover table-borderless">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>API Key</th>
                            <th>Status</th>
                            <th>Last Used</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($api_keys, 0, 3) as $api_key): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($api_key['name']); ?></td>
                                <td class="api-key-cell">
                                    <div class="input-group input-group-sm">
                                        <input type="text" class="form-control form-control-sm" value="<?php echo htmlspecialchars($api_key['api_key']); ?>" readonly>
                                        <button class="btn btn-outline-secondary copy-btn" type="button" data-copy="<?php echo htmlspecialchars($api_key['api_key']); ?>">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($api_key['status'] === 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo $api_key['last_used_at'] ? formatDate($api_key['last_used_at']) : 'Never used'; ?>
                                </td>
                                <td>
                                    <a href="<?php echo BASE_URL; ?>/dashboard/api-keys.php" class="btn btn-sm btn-outline-primary">
                                        Details
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if (count($api_keys) > 3): ?>
                <div class="text-center mt-3">
                    <a href="<?php echo BASE_URL; ?>/dashboard/api-keys.php" class="btn btn-sm btn-outline-primary">
                        View All API Keys
                    </a>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="text-center py-4">
                <div class="mb-3 text-muted">
                    <i class="fas fa-key fa-3x"></i>
                </div>
                <p>No API Keys yet</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php // include_once '../templates/dashboard/footer.php'; 
include_once '../templates/footer.php';  ?> 