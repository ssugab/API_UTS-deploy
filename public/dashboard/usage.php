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

// Get usage statistics
$stats = getUserUsageStats($_SESSION['user_id']);

$page_title = 'Usage Statistics';
$active_sidebar = 'usage';

// include_once '../templates/dashboard/header.php';
include_once '../templates/header.php';
?>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title text-muted mb-3">Total Requests</h5>
                <h2 class="mb-0"><?php echo number_format($stats['total_requests']); ?></h2>
                <p class="text-muted mb-0">All time</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title text-muted mb-3">Successful Requests</h5>
                <h2 class="mb-0"><?php echo number_format($stats['successful_requests']); ?></h2>
                <p class="text-muted mb-0"><?php echo round(($stats['successful_requests'] / max(1, $stats['total_requests'])) * 100); ?>% success rate</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title text-muted mb-3">Failed Requests</h5>
                <h2 class="mb-0"><?php echo number_format($stats['failed_requests']); ?></h2>
                <p class="text-muted mb-0"><?php echo round(($stats['failed_requests'] / max(1, $stats['total_requests'])) * 100); ?>% failure rate</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i> Recent Activity</h5>
            </div>
            <div class="card-body">
                <?php if (count($stats['recent_activity']) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Endpoint</th>
                                    <th>Status</th>
                                    <th>Response Time</th>
                                    <th>Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats['recent_activity'] as $activity): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($activity['endpoint']); ?></td>
                                        <td>
                                            <?php if ($activity['status'] >= 200 && $activity['status'] < 300): ?>
                                                <span class="badge bg-success"><?php echo $activity['status']; ?></span>
                                            <?php else: ?>
                                                <span class="badge bg-danger"><?php echo $activity['status']; ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <!-- <td><?php // echo number_format($activity['response_time'], 2); ?>ms</td> -->
                                        <td><?php echo formatDate($activity['timestamp']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="fas fa-chart-line fa-3x text-muted"></i>
                        </div>
                        <h5>No Recent Activity</h5>
                        <p class="text-muted">Your API usage statistics will appear here.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i> Endpoint Usage</h5>
            </div>
            <div class="card-body">
                <?php if (count($stats['endpoint_usage']) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Endpoint</th>
                                    <th>Total Requests</th>
                                    <th>Success Rate</th>
                                    <th>Average Response Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats['endpoint_usage'] as $endpoint): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($endpoint['endpoint']); ?></td>
                                        <td><?php echo number_format($endpoint['total_requests']); ?></td>
                                        <td><?php echo round($endpoint['success_rate']); ?>%</td>
                                        <td><?php echo number_format($endpoint['avg_response_time'], 2); ?>ms</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="fas fa-chart-pie fa-3x text-muted"></i>
                        </div>
                        <h5>No Endpoint Usage Data</h5>
                        <p class="text-muted">Endpoint usage statistics will appear here.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php // include_once '../templates/dashboard/footer.php'; 
include_once '../templates/footer.php';  ?> 