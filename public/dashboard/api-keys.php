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

// Get user's API keys
$api_keys = getUserApiKeys($_SESSION['user_id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $name = sanitize($_POST['name']);
                if (empty($name)) {
                    redirect('/dashboard/api-keys.php', 'API Key name is required.', 'warning');
                }
                
                $api_key = createApiKey($_SESSION['user_id'], $name);
                if ($api_key) {
                    redirect('/dashboard/api-keys.php', 'API Key created successfully.', 'success');
                } else {
                    redirect('/dashboard/api-keys.php', 'Failed to create API Key.', 'error');
                }
                break;
                
            case 'delete':
                $api_key_id = (int)$_POST['api_key_id'];
                $stmt = $db->prepare("DELETE FROM api_keys WHERE id = ? AND user_id = ?");
                if ($stmt->execute([$api_key_id, $_SESSION['user_id']])) {
                    redirect('/dashboard/api-keys.php', 'API Key deleted successfully.', 'success');
                } else {
                    redirect('/dashboard/api-keys.php', 'Failed to delete API Key.', 'error');
                }
                break;
                
            case 'toggle':
                $api_key_id = (int)$_POST['api_key_id'];
                $stmt = $db->prepare("UPDATE api_keys SET status = IF(status = 'active', 'inactive', 'active') WHERE id = ? AND user_id = ?");
                if ($stmt->execute([$api_key_id, $_SESSION['user_id']])) {
                    redirect('/dashboard/api-keys.php', 'API Key status updated successfully.', 'success');
                } else {
                    redirect('/dashboard/api-keys.php', 'Failed to update API Key status.', 'error');
                }
                break;
        }
    }
}

$page_title = 'API Keys';
$active_sidebar = 'api-keys';

// include_once '../templates/dashboard/header.php';
include_once '../templates/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-key me-2"></i> API Keys</h5>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createApiKeyModal">
                    <i class="fas fa-plus me-2"></i> Create New API Key
                </button>
            </div>
            <div class="card-body">
                <?php if (count($api_keys) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>API Key</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Last Used</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($api_keys as $key): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($key['name']); ?></td>
                                        <td>
                                            <div class="input-group">
                                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($key['api_key']); ?>" readonly>
                                                <button class="btn btn-outline-secondary copy-btn" type="button" data-copy="<?php echo htmlspecialchars($key['api_key']); ?>">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($key['status'] === 'active'): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo formatDate($key['created_at']); ?></td>
                                        <td><?php echo $key['last_used_at'] ? formatDate($key['last_used_at']) : 'Never'; ?></td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="toggle">
                                                <input type="hidden" name="api_key_id" value="<?php echo $key['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                                    <?php echo $key['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                                                </button>
                                            </form>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="api_key_id" value="<?php echo $key['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this API Key?');">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="fas fa-key fa-3x text-muted"></i>
                        </div>
                        <h5>No API Keys Found</h5>
                        <p class="text-muted">Create your first API Key to start using the API.</p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createApiKeyModal">
                            <i class="fas fa-plus me-2"></i> Create API Key
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Create API Key Modal -->
<div class="modal fade" id="createApiKeyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <div class="modal-header">
                    <h5 class="modal-title">Create New API Key</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">API Key Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <div class="form-text">Give your API Key a descriptive name to help you identify it later.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create API Key</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php // include_once '../templates/dashboard/footer.php'; 
include_once '../templates/footer.php';  ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Menangani tombol copy
    document.querySelectorAll('.copy-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            const apiKey = this.getAttribute('data-copy');
            
            // Buat elemen input sementara
            const tempInput = document.createElement('input');
            tempInput.value = apiKey;
            document.body.appendChild(tempInput);
            
            // Pilih dan copy teks
            tempInput.select();
            document.execCommand('copy');
            
            // Hapus elemen input sementara
            document.body.removeChild(tempInput);
            
            // Ubah ikon menjadi centang
            const icon = button.querySelector('i');
            icon.classList.remove('fa-copy');
            icon.classList.add('fa-check');
            
            // Ubah warna tombol menjadi hijau
            button.classList.remove('btn-outline-secondary');
            button.classList.add('btn-success');
            
            // Kembalikan ikon dan warna setelah 2 detik
            setTimeout(function() {
                icon.classList.remove('fa-check');
                icon.classList.add('fa-copy');
                button.classList.remove('btn-success');
                button.classList.add('btn-outline-secondary');
            }, 2000);
        });
    });
});
</script> 