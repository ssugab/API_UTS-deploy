<?php
// Check if user is logged in
if (!isLoggedIn()) {
    redirect(BASE_URL . '/auth/login.php', 'Please login to continue', 'warning');
}
?>
<div class="sidebar-header p-3 border-bottom">
    <h5 class="mb-0">Dashboard Menu</h5>
</div>
<div class="sidebar-content p-3">
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?php echo $active_sidebar === 'dashboard' ? 'active' : ''; ?>" 
               href="<?php echo BASE_URL; ?>/dashboard/dashboard.php">
                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $active_sidebar === 'api-keys' ? 'active' : ''; ?>" 
               href="<?php echo BASE_URL; ?>/dashboard/api-keys.php">
                <i class="fas fa-key me-2"></i> API Keys
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $active_sidebar === 'usage' ? 'active' : ''; ?>" 
               href="<?php echo BASE_URL; ?>/dashboard/usage.php">
                <i class="fas fa-chart-line me-2"></i> Usage Statistics
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $active_sidebar === 'documentation' ? 'active' : ''; ?>" 
               href="<?php echo BASE_URL; ?>/dashboard/documentation.php">
                <i class="fas fa-book me-2"></i> Documentation
            </a>
        </li>
        <!-- Temporarily disabled until implemented -->
        <!--
        <li class="nav-item">
            <a class="nav-link <?php //echo $active_sidebar === 'profile' ? 'active' : ''; ?>" 
               href="<?php //echo BASE_URL; ?>/dashboard/profile.php">
                <i class="fas fa-user me-2"></i> Profile
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php //echo $active_sidebar === 'settings' ? 'active' : ''; ?>" 
               href=" <?php // echo BASE_URL; ?>/dashboard/settings.php">
                <i class="fas fa-cog me-2"></i> Settings
            </a>
        </li>
        -->
    </ul>
</div> 