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

// Inisialisasi variabel untuk pesan dan input
$errors = [];
$success_message = '';
$input = [
    'name' => $user['full_name'],
    'email' => $user['email'],
    'current_password' => '',
    'new_password' => '',
    'confirm_password' => ''
];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $input['name'] = trim($_POST['name']);
        $input['email'] = trim($_POST['email']);
        
        if (empty($input['name'])) {
            $errors['name'] = 'Full name is required.';
        }
        if (empty($input['email'])) {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format.';
        }
        
        if (empty($errors)) {
            if (updateUserProfile($_SESSION['user_id'], $input['name'], $input['email'])) {
                $success_message = 'Profile updated successfully.';
                // Perbarui data pengguna untuk refleksi di form
                $user['full_name'] = $input['name'];
                $user['email'] = $input['email'];
            } else {
                $errors['general'] = 'Failed to update profile.';
            }
        }
    }
    
    if (isset($_POST['change_password'])) {
        $input['current_password'] = $_POST['current_password'];
        $input['new_password'] = $_POST['new_password'];
        $input['confirm_password'] = $_POST['confirm_password'];
        
        if (empty($input['current_password'])) {
            $errors['current_password'] = 'Current password is required.';
        }
        if (empty($input['new_password'])) {
            $errors['new_password'] = 'New password is required.';
        }
        if (empty($input['confirm_password'])) {
            $errors['confirm_password'] = 'Confirm password is required.';
        }
        
        if (empty($errors)) {
            if ($input['new_password'] !== $input['confirm_password']) {
                $errors['confirm_password'] = 'New passwords do not match.';
            }
            if (strlen($input['new_password']) < 8) {
                $errors['new_password'] = 'New password must be at least 8 characters long.';
            }
        }
        
        if (empty($errors)) {
            if (changeUserPassword($_SESSION['user_id'], $input['current_password'], $input['new_password'])) {
                $success_message = 'Password changed successfully.';
                // Reset field kata sandi setelah sukses
                $input['current_password'] = '';
                $input['new_password'] = '';
                $input['confirm_password'] = '';
            } else {
                $errors['current_password'] = 'Current password is incorrect.';
            }
        }
    }
}

$page_title = 'Account Settings';
$active_sidebar = 'settings';

// include_once '../templates/dashboard/header.php';
include_once '../templates/header.php';
?>

<div class="row">
    <!-- Tampilkan pesan sukses -->
    <?php if ($success_message): ?>
        <div class="col-12 mb-4">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Tampilkan pesan error umum -->
    <?php if (isset($errors['general'])): ?>
        <div class="col-12 mb-4">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($errors['general']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <div class="col-md-6">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i> Profile Settings</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="update_profile" value="1">
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" 
                               id="name" name="name" value="<?php echo htmlspecialchars($input['name']); ?>" required>
                        <?php if (isset($errors['name'])): ?>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['name']); ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                               id="email" name="email" value="<?php echo htmlspecialchars($input['email']); ?>" required>
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['email']); ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-lock me-2"></i> Change Password</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="change_password" value="1">
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control <?php echo isset($errors['current_password']) ? 'is-invalid' : ''; ?>" 
                               id="current_password" name="current_password" value="<?php echo htmlspecialchars($input['current_password']); ?>" required>
                        <?php if (isset($errors['current_password'])): ?>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['current_password']); ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control <?php echo isset($errors['new_password']) ? 'is-invalid' : ''; ?>" 
                               id="new_password" name="new_password" value="<?php echo htmlspecialchars($input['new_password']); ?>" required>
                        <div class="form-text">Password must be at least 8 characters long.</div>
                        <?php if (isset($errors['new_password'])): ?>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['new_password']); ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                               id="confirm_password" name="confirm_password" value="<?php echo htmlspecialchars($input['confirm_password']); ?>" required>
                        <?php if (isset($errors['confirm_password'])): ?>
                            <div class="invalid-feedback"><?php echo htmlspecialchars($errors['confirm_password']); ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-trash-alt me-2"></i> Delete Account</h5>
            </div>
            <div class="card-body">
                <p class="text-danger">Warning: This action cannot be undone. All your data will be permanently deleted.</p>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                    Delete My Account
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteAccountModalLabel">Confirm Account Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete your account? This action cannot be undone.</p>
                <p class="text-danger">All your data, including API keys and usage history, will be permanently deleted.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="/dashboard/delete-account.php" class="d-inline">
                    <button type="submit" class="btn btn-danger">Delete Account</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php 
// include_once '../templates/dashboard/footer.php'; 
include_once '../templates/footer.php'; 
?>