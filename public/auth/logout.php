<?php
require_once '../config/config.php';

// Logout user
if (isLoggedIn()) {
    logoutUser();
    redirect(BASE_URL . '/auth/login.php', 'Anda berhasil logout', 'success');
} else {
    redirect(BASE_URL);
}
?>