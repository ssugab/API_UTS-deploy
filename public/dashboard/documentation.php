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

$page_title = 'API Documentation';
$active_sidebar = 'documentation';

// include_once '../templates/dashboard/header.php';
include_once '../templates/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-book me-2"></i> API Documentation</h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <h4>Base URL</h4>
                    <p>All API endpoints are relative to the base URL:</p>
                    <code><?php echo BASE_URL; ?>/api/</code>
                </div>

                <div class="mb-4">
                    <h4>Authentication</h4>
                    <p>All API requests require an API key. Include your API key in the request header:</p>
                    <code>X-API-Key: your_api_key_here</code>
                </div>

                <div class="mb-4">
                    <h4>Indonesian Address API</h4>
                    <p>This API provides access to Indonesian address data with 5 levels of hierarchy:</p>
                    <ol>
                        <li>Province (Provinsi)</li>
                        <li>City/District (Kabupaten/Kota)</li>
                        <li>Sub-district (Kecamatan)</li>
                        <li>Village (Kelurahan)</li>
                        <li>Postal Code (Kode Pos)</li>
                    </ol>

                    <h5 class="mt-4">Endpoints</h5>

                    <div class="card mb-3">
                        <div class="card-body">
                            <h6>Get Provinces</h6>
                            <code>GET /address/provinces</code>
                            <p class="mt-2">Returns a list of all provinces in Indonesia.</p>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-body">
                            <h6>Get Cities by Province</h6>
                            <code>GET /address/cities?province_id={id}</code>
                            <p class="mt-2">Returns cities/districts for a specific province.</p>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-body">
                            <h6>Get Sub-districts by City</h6>
                            <code>GET /address/subdistricts?city_id={id}</code>
                            <p class="mt-2">Returns sub-districts for a specific city.</p>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-body">
                            <h6>Get Villages by Sub-district</h6>
                            <code>GET /address/villages?subdistrict_id={id}</code>
                            <p class="mt-2">Returns villages for a specific sub-district.</p>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-body">
                            <h6>Get Postal Codes</h6>
                            <code>GET /address/postal-codes?city_id={id}&subdistrict_id={id}</code>
                            <p class="mt-2">Returns postal codes for a specific city and sub-district.</p>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-body">
                            <h6>Search Address</h6>
                            <code>GET /address/search?keyword={keyword}</code>
                            <p class="mt-2">Search for addresses by keyword.</p>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <h4>Response Format</h4>
                    <p>All responses are in JSON format with the following structure:</p>
                    <pre><code>{
    "status": 200,
    "message": "Success",
    "data": [
        {
            "id": "1",
            "name": "Example"
        }
    ]
}</code></pre>
                </div>

                <div class="mb-4">
                    <h4>Error Handling</h4>
                    <p>Error responses follow this format:</p>
                    <pre><code>{
    "status": 400,
    "message": "Error message",
    "errors": {
        "field": ["Error message"]
    }
}</code></pre>
                </div>
            </div>
        </div>
    </div>
</div>

<?php // include_once '../templates/dashboard/footer.php'; 
            include_once '../templates/footer.php'; 
?> 