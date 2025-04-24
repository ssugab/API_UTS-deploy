<?php
require_once 'config/config.php';

// Page title
$page_title = "Welcome to " . SITE_NAME;

include_once 'templates/header.php';
?>

<div class="container-fluid px-0">
    <!-- Hero Section -->
    <section class="hero bg-gradient-primary text-white py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-3">Address API Provider</h1>
                    <p class="lead mb-4">Access comprehensive Indonesian address data through our reliable and easy-to-use API service.</p>
                    <?php if (!isLoggedIn()): ?>
                        <div class="d-flex gap-3">
                            <a href="<?php echo BASE_URL; ?>/auth/register.php" class="btn btn-light btn-lg">Get Started</a>
                            <a href="<?php echo BASE_URL; ?>/auth/login.php" class="btn btn-outline-light btn-lg">Sign In</a>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>/dashboard/dashboard.php" class="btn btn-light btn-lg">Go to Dashboard</a>
                    <?php endif; ?>
                </div>
                <div class="col-lg-6 d-none d-lg-block">
                    <div class="p-4 bg-white rounded-3 shadow-lg">
                        <pre class="mb-0"><code class="language-json">{
  "province": "DKI Jakarta",
  "city": "Jakarta Selatan",
  "district": "Kebayoran Baru",
  "subdistrict": "Senayan",
  "postal_code": "12190"
}</code></pre>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features py-5">
        <div class="container">
            <h2 class="text-center mb-5">Why Choose Our API?</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon bg-primary text-white rounded-circle mb-3 mx-auto">
                                <i class="fas fa-bolt"></i>
                            </div>
                            <h3 class="h5 mb-3">Fast & Reliable</h3>
                            <p class="text-muted mb-0">High-performance API with 99.9% uptime guarantee and fast response times.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon bg-success text-white rounded-circle mb-3 mx-auto">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h3 class="h5 mb-3">Secure & Scalable</h3>
                            <p class="text-muted mb-0">Enterprise-grade security with API key authentication and flexible rate limits.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon bg-info text-white rounded-circle mb-3 mx-auto">
                                <i class="fas fa-database"></i>
                            </div>
                            <h3 class="h5 mb-3">Complete Data</h3>
                            <p class="text-muted mb-0">Comprehensive Indonesian address data, regularly updated and validated.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Documentation Preview -->
    <section id="documentation" class="documentation bg-light py-5">
        <div class="container">
            <h2 class="text-center mb-5">Easy to Implement</h2>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-dark text-white py-3">
                            <h5 class="card-title mb-0"><i class="fab fa-php me-2"></i>PHP Example</h5>
                        </div>
                        <div class="card-body bg-dark">
                            <pre class="text-white mb-0"><code>$apiKey = 'YOUR_API_KEY';
$url = '<?php echo API_BASE_URL; ?>/v1/provinces';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $apiKey"
]);

$response = curl_exec($ch);
$data = json_decode($response, true);</code></pre>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-dark text-white py-3">
                            <h5 class="card-title mb-0"><i class="fab fa-js me-2"></i>JavaScript Example</h5>
                        </div>
                        <div class="card-body bg-dark">
                            <pre class="text-white mb-0"><code>const apiKey = 'YOUR_API_KEY';
const url = '<?php echo API_BASE_URL; ?>/v1/provinces';

fetch(url, {
    headers: {
        'Authorization': `Bearer ${apiKey}`
    }
})
.then(response => response.json())
.then(data => {
    console.log(data);
});</code></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="pricing py-5">
        <div class="container">
            <h2 class="text-center mb-5">Simple, Transparent Pricing</h2>
            <div class="row justify-content-center g-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4 text-center">
                            <h3 class="h5 mb-4">Free Tier</h3>
                            <div class="display-4 mb-4">Rp 0</div>
                            <ul class="list-unstyled mb-4">
                                <li class="mb-3"><i class="fas fa-check text-success me-2"></i>1,000 requests/day</li>
                                <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Basic endpoints</li>
                                <li class="mb-3"><i class="fas fa-check text-success me-2"></i>API key authentication</li>
                            </ul>
                            <a href="<?php echo BASE_URL; ?>/auth/register.php" class="btn btn-outline-primary">Get Started</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-lg border-primary">
                        <div class="card-body p-4 text-center">
                            <h3 class="h5 mb-4">Pro Tier</h3>
                            <div class="display-4 mb-4">Rp 99k</div>
                            <ul class="list-unstyled mb-4">
                                <li class="mb-3"><i class="fas fa-check text-success me-2"></i>10,000 requests/day</li>
                                <li class="mb-3"><i class="fas fa-check text-success me-2"></i>All endpoints</li>
                                <li class="mb-3"><i class="fas fa-check text-success me-2"></i>Priority support</li>
                            </ul>
                            <a href="<?php echo BASE_URL; ?>/auth/register.php" class="btn btn-primary">Get Started</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta bg-gradient-primary text-white py-5">
        <div class="container text-center">
            <h2 class="mb-4">Ready to Get Started?</h2>
            <p class="lead mb-4">Create an account now and get your API key in minutes.</p>
            <?php if (!isLoggedIn()): ?>
                <a href="<?php echo BASE_URL; ?>/auth/register.php" class="btn btn-light btn-lg">Sign Up Now</a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>/dashboard/dashboard.php" class="btn btn-light btn-lg">Go to Dashboard</a>
            <?php endif; ?>
        </div>
    </section>
</div>

<!-- Add custom styles -->
<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
}

.feature-icon {
    width: 64px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.hero {
    position: relative;
    overflow: hidden;
}

.hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('<?php echo BASE_URL; ?>/assets/img/hero-pattern.svg') center/cover;
    opacity: 0.1;
}

pre {
    background: #2d3748;
    border-radius: 8px;
    padding: 1rem;
}

code {
    color: #e2e8f0;
}

.pricing .card:hover {
    transform: translateY(-5px);
    transition: transform 0.3s ease;
}
</style>

<?php include_once 'templates/footer.php'; ?> 