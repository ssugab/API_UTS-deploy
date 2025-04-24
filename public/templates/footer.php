</div><!-- End of container -->

    <footer class="bg-light text-black py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <h5>Indonesia Address API</h5>
                    <p class="text-muted">
                        API penyedia data alamat di Indonesia yang mudah digunakan.
                        Dapatkan data provinsi, kabupaten/kota, kecamatan, kelurahan, dan kode pos.
                    </p>
                </div>
                <div class="col-md-4 mb-3">
                    <h5>Tautan</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo BASE_URL; ?>" class="text-muted">Beranda</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/dashboard/documentation.php" class="text-muted">Dokumentasi</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/auth/register.php" class="text-muted">Registrasi</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/auth/login.php" class="text-muted">Login</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Kontak</h5>
                    <ul class="list-unstyled text-muted">
                        <li><i class="fas fa-envelope me-2"></i> support@alamat-api.id</li>
                        <li><i class="fab fa-github me-2"></i> <a href="https://github.com/your-username/address-api-provider" class="text-muted">GitHub Repository</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-3 bg-secondary">
            <div class="text-center">
                <p class="mb-0">© <?php echo date('Y'); ?> Indonesia Address API Provider. All rights reserved.</p>
                <p class="small text-muted">Dibuat dengan <i class="fas fa-heart text-danger"></i> di Indonesia</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Custom JS -->
    <script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="<?php echo BASE_URL; ?>/assets/js/script.js"></script>
    
    <?php if (isset($extra_js) && is_array($extra_js)): ?>
        <?php foreach ($extra_js as $js_file): ?>
            <script src="<?php echo BASE_URL; ?>/assets/js/<?php echo $js_file; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <script>
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Flash message auto-hide
        window.setTimeout(function() {
            document.querySelectorAll(".alert").forEach(function(alert) {
                if (!alert.classList.contains('alert-permanent')) {
                    bootstrap.Alert.getOrCreateInstance(alert).close();
                }
            });
        }, 5000);
    </script>
</body>
</html>