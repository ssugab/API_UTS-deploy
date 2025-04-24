<?php
// Check if user is logged in
if (!isLoggedIn()) {
    redirect(BASE_URL . '/auth/login.php', 'Please login to continue', 'warning');
}
?>
<div class="sidebar bg-white shadow-sm rounded p-3">
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?php echo $active_sidebar === 'dashboard' ? 'active' : ''; ?>" 
               href="<?php echo BASE_URL; ?>/dashboard/">
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
                <i class="fas fa-chart-line me-2"></i> Statistik Penggunaan
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $active_sidebar === 'documentation' ? 'active' : ''; ?>" 
               href="<?php echo BASE_URL; ?>/dashboard/documentation.php">
                <i class="fas fa-book me-2"></i> Dokumentasi
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $active_sidebar === 'profile' ? 'active' : ''; ?>" 
               href="<?php echo BASE_URL; ?>/dashboard/profile.php">
                <i class="fas fa-user me-2"></i> Profil
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $active_sidebar === 'settings' ? 'active' : ''; ?>" 
               href="<?php echo BASE_URL; ?>/dashboard/settings.php">
                <i class="fas fa-cog me-2"></i> Pengaturan
            </a>
        </li>
        <li class="nav-item mt-3">
            <a class="nav-link text-danger" href="<?php echo BASE_URL; ?>/auth/logout.php">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
            </a>
        </li>
    </ul>
</div>