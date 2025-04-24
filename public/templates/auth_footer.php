    </div> <!-- End container -->

    <!-- Footer -->
    <footer class="footer mt-auto py-3 bg-white border-top">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5>Tentang Kami</h5>
                    <p>API Provider untuk data alamat di Indonesia. Menyediakan data yang akurat dan mudah diakses.</p>
                </div>
                <div class="col-lg-2 mb-4">
                    <h5>Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo BASE_URL; ?>#features">Fitur</a></li>
                        <li><a href="<?php echo BASE_URL; ?>#documentation">Dokumentasi</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/auth/login.php">Login</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 mb-4">
                    <h5>Kontak</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-envelope me-2"></i> support@alamat-api.id</li>
                        <li><i class="fab fa-github me-2"></i> GitHub</li>
                    </ul>
                </div>
                <div class="col-lg-3 mb-4">
                    <h5>Newsletter</h5>
                    <p>Dapatkan update terbaru dari kami</p>
                    <form class="mb-3">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Email Anda">
                            <button class="btn btn-primary" type="submit">Subscribe</button>
                        </div>
                    </form>
                </div>
            </div>
            <hr class="my-4">
            <div class="text-center">
                <p class="mb-0">&copy; 2024 API Alamat Indonesia. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <?php if (isset($extra_js) && is_array($extra_js)): ?>
        <?php foreach ($extra_js as $js_file): ?>
            <script src="<?php echo BASE_URL; ?>/assets/js/<?php echo $js_file; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html> 