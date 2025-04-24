<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn()) {
    error_log("User not logged in, redirecting to login from profile.php");
    redirect('/auth/login.php', 'Please login to access the dashboard.', 'warning');
}

$user = getUserById($_SESSION['user_id']);
if (!$user) {
    error_log("User not found for ID {$_SESSION['user_id']}, clearing session and redirecting");
    unset($_SESSION['user_id']);
    session_destroy();
    redirect('/auth/login.php', 'User not found. Session cleared.', 'error');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - API Provider</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>
    <?php // include_once '../templates/dashboard/header.php';
            include_once '../templates/header.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <main class="col-md-9 col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Profile</h1>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">User Information</h5>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($user['full_name']); ?></p>
                        <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                        <p><strong>Joined:</strong> <?php echo formatDate($user['created_at']); ?></p>
                    </div>
                </div>
                <a href="<?php echo BASE_URL; ?>/dashboard/settings.php" class="btn btn-primary mt-3">Edit Profile</a>
            </main>
        </div>
    </div>
    <?php // include_once '../templates/dashboard/footer.php'; 
            include_once '../templates/footer.php'; ?>
</body>
</html>