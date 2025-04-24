<?php
if (isset($_SESSION['flash_message']) && isset($_SESSION['flash_type'])) {
    $icon = '';
    switch ($_SESSION['flash_type']) {
        case 'success':
            $icon = '<i class="fas fa-check-circle"></i>';
            break;
        case 'warning':
            $icon = '<i class="fas fa-exclamation-triangle"></i>';
            break;
        case 'danger':
            $icon = '<i class="fas fa-times-circle"></i>';
            break;
        case 'info':
            $icon = '<i class="fas fa-info-circle"></i>';
            break;
    }
?>
    <div class="alert alert-<?php echo $_SESSION['flash_type']; ?> alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <div class="me-3">
                <?php echo $icon; ?>
            </div>
            <div>
                <?php echo $_SESSION['flash_message']; ?>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php
    unset($_SESSION['flash_message']);
    unset($_SESSION['flash_type']);
}
?> 