<?php
// Define page title for the header
define('PAGE_TITLE', 'Dashboard');

// Include required configuration and function files
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Check if user is logged in, redirect to login if not
require_login();

// Start output buffering to capture the content
ob_start();
?>

<!-- Custom Loader HTML -->
<div class="spinnerContainer" id="customLoader">
    <div class="spinner"></div>
    <div class="loader">
        <p>loading</p>
        <div class="words">
            <span class="word">reports</span>
            <span class="word">data</span>
            <span class="word">incidents</span>
            <span class="word">assistance</span>
            <span class="word">reports</span>
        </div>
    </div>
</div>

<!-- Dashboard Content -->
<style>
    .page-header {
        padding: 30px;
        background: white;
        margin: 20px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }
    .page-header h1 {
        color: #0d6efd;
        font-weight: 700;
        margin-bottom: 10px;
    }
    .page-header p {
        color: #6c757d;
        font-size: 1.1rem;
        margin-bottom: 0;
    }
    .dashboard-cards {
        padding: 0 20px 20px;
    }
    .card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s, box-shadow 0.3s;
        margin-bottom: 20px;
    }
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }
    .card-body {
        padding: 25px;
    }
    .card-icon {
        font-size: 2.5rem;
        margin-bottom: 15px;
        opacity: 0.8;
    }
    .card-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 10px;
    }
    .card-text {
        color: #6c757d;
        font-size: 0.9rem;
        margin-bottom: 15px;
    }
    .card-link {
        color: #0d6efd;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }
    .card-link i {
        margin-left: 5px;
        transition: transform 0.3s;
    }
    .card-link:hover i {
        transform: translateX(5px);
    }

    /* Custom Loader Styles */
    .spinnerContainer {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.95);
        z-index: 9999;
        backdrop-filter: blur(5px);
    }

    .spinner {
        width: 56px;
        height: 56px;
        display: grid;
        border: 4px solid #0000;
        border-radius: 50%;
        border-right-color: #299fff;
        animation: tri-spinner 1s infinite linear;
    }

    .spinner::before,
    .spinner::after {
        content: "";
        grid-area: 1/1;
        margin: 2px;
        border: inherit;
        border-radius: 50%;
        animation: tri-spinner 2s infinite;
    }

    .spinner::after {
        margin: 8px;
        animation-duration: 3s;
    }

    @keyframes tri-spinner {
        100% {
            transform: rotate(1turn);
        }
    }

    .loader {
        color: #4a4a4a;
        font-family: "Poppins",sans-serif;
        font-weight: 500;
        font-size: 25px;
        -webkit-box-sizing: content-box;
        box-sizing: content-box;
        height: 40px;
        padding: 10px 10px;
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        border-radius: 8px;
    }

    .words {
        overflow: hidden;
    }

    .word {
        display: block;
        height: 100%;
        padding-left: 6px;
        color: #299fff;
        animation: cycle-words 5s infinite;
    }

    @keyframes cycle-words {
        10% {
            -webkit-transform: translateY(-105%);
            transform: translateY(-105%);
        }

        25% {
            -webkit-transform: translateY(-100%);
            transform: translateY(-100%);
        }

        35% {
            -webkit-transform: translateY(-205%);
            transform: translateY(-205%);
        }

        50% {
            -webkit-transform: translateY(-200%);
            transform: translateY(-200%);
        }

        60% {
            -webkit-transform: translateY(-305%);
            transform: translateY(-305%);
        }

        75% {
            -webkit-transform: translateY(-300%);
            transform: translateY(-300%);
        }

        85% {
            -webkit-transform: translateY(-405%);
            transform: translateY(-405%);
        }

        100% {
            -webkit-transform: translateY(-400%);
            transform: translateY(-400%);
        }
    }

    /* Skeleton Loader Styles */
    .skeleton {
        background: #f6f7f8;
        border-radius: 4px;
        position: relative;
        overflow: hidden;
    }
    .skeleton::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
        animation: shimmer 1.5s infinite;
    }
    @keyframes shimmer {
        100% {
            transform: translateX(100%);
        }
    }
    .skeleton-card {
        height: 200px; /* Matches card height */
        margin-bottom: 20px;
    }
    .skeleton-title {
        width: 60%;
        height: 20px;
        margin-bottom: 10px;
    }
    .skeleton-text {
        width: 80%;
        height: 15px;
        margin-bottom: 10px;
    }
    .skeleton-icon {
        width: 40px;
        height: 40px;
        margin-bottom: 15px;
    }
    .skeleton-link {
        width: 30%;
        height: 15px;
    }
    .skeleton-list-item {
        height: 40px;
        margin-bottom: 10px;
    }
    .content-hidden {
        display: none;
    }
</style>

<!-- Page Header -->
<div class="page-header">
    <div class="container-fluid">
        <h1>Dashboard</h1>
        <p>Welcome to the MDRRM-ARMS (Municipal Disaster Risk Reduction and Management – Annex Reporting and Monitoring System). Select a report from the sidebar to get started.</p>
        <div class="mt-3">
            <small class="text-muted">
                Logged in as: <strong><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?></strong>
                (<?php echo htmlspecialchars($_SESSION['user_role'] ?? 'User'); ?>)
                <?php if (!empty($_SESSION['barangay'])): ?>
                    - : <strong><?php echo htmlspecialchars($_SESSION['barangay']); ?></strong>
                <?php endif; ?>
            </small>
        </div>
    </div>
</div>

<!-- Dashboard Cards -->
 <!-- In admin dashboard -->
<div class="card">
    <div class="card-header">Quick Actions</div>
    <div class="card-body">
        <a href="barangay_locking.php" class="btn btn-primary me-2">
            <i class="fas fa-lock me-1"></i>Manage Barangay Locks
        </a>
        <!-- Other dashboard links -->
    </div>
</div>
<div class="dashboard-cards">
    <div class="container-fluid">
        <div class="row skeleton-container">
            <!-- Skeleton Loaders for Cards -->
            <div class="col-md-3">
                <div class="card skeleton skeleton-card"></div>
            </div>
            <div class="col-md-3">
                <div class="card skeleton skeleton-card"></div>
            </div>
            <div class="col-md-3">
                <div class="card skeleton skeleton-card"></div>
            </div>
            <div class="col-md-3">
                <div class="card skeleton skeleton-card"></div>
            </div>
        </div>
        <div class="row content-container content-hidden">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="card-icon text-primary">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <h5 class="card-title">Incident Reports</h5>
                        <p class="card-text">Report related incidents and casualty information for disaster monitoring.</p>
                        <a href="#incidentsSubmenu" class="card-link" data-bs-toggle="collapse">
                            View Reports <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="card-icon text-warning">
                            <i class="bi bi-house"></i>
                        </div>
                        <h5 class="card-title">Damage Assessment</h5>
                        <p class="card-text">Document damages to houses, agriculture, infrastructure, and other assets.</p>
                        <a href="#damageSubmenu" class="card-link" data-bs-toggle="collapse">
                            View Reports <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="card-icon text-info">
                            <i class="bi bi-clipboard-check"></i>
                        </div>
                        <h5 class="card-title">Status Reports</h5>
                        <p class="card-text">Monitor status of roads, bridges, power, communication, and work suspension.</p>
                        <a href="#statusSubmenu" class="card-link" data-bs-toggle="collapse">
                            View Reports <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="card-icon text-success">
                            <i class="bi bi-heart"></i>
                        </div>
                        <h5 class="card-title">Assistance Reports</h5>
                        <p class="card-text">Track assistance provided to affected families, LGUs, and agencies.</p>
                        <a href="#assistanceSubmenu" class="card-link" data-bs-toggle="collapse">
                            View Reports <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Quick Start Guide</h5>
                        <p class="card-text">
                            This system follows the NDRRMC Memorandum Circular No. 05, s. 2025 reporting templates.
                            To submit a report, select the appropriate annex from the sidebar navigation. Each form
                            includes technical notes to guide you through the reporting process.
                        </p>
                        <div class="row mt-4">
                            <div class="col-md-4">
                                <h6><i class="bi bi-1-circle text-primary me-2"></i>Select Report</h6>
                                <p class="small text-muted">Choose the appropriate annex from the sidebar menu.</p>
                            </div>
                            <div class="col-md-4">
                                <h6><i class="bi bi-2-circle text-primary me-2"></i>Fill Out Form</h6>
                                <p class="small text-muted">Complete all required fields with accurate information.</p>
                            </div>
                            <div class="col-md-4">
                                <h6><i class="bi bi-3-circle text-primary me-2"></i>Submit Report</h6>
                                <p class="small text-muted">Review and save your report for official submission.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Recent Activity</h5>
                        <div class="list-group list-group-flush skeleton-container">
                            <div class="list-group-item skeleton skeleton-list-item"></div>
                            <div class="list-group-item skeleton skeleton-list-item"></div>
                        </div>
                        <div class="list-group list-group-flush content-container content-hidden">
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    <span>You logged in successfully</span>
                                </div>
                                <small class="text-muted">Just now</small>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-info-circle text-primary me-2"></i>
                                    <span>Welcome to the MDRRM-ARMS</span>
                                </div>
                                <small class="text-muted">Today</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Loaders -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Hide custom loader after short delay
        setTimeout(function () {
            const customLoader = document.getElementById('customLoader');
            if (customLoader) {
                customLoader.style.display = 'none';
            }
        }, 2000); // 2 seconds for custom loader

        // Simulate loading delay for skeleton loaders (replace with actual data fetch if needed)
        setTimeout(function () {
            // Hide skeleton loaders
            document.querySelectorAll('.skeleton-container').forEach(function (container) {
                container.style.display = 'none';
            });
            // Show actual content
            document.querySelectorAll('.content-container').forEach(function (container) {
                container.classList.remove('content-hidden');
            });
        }, 3000); // 1.5 seconds delay for skeletons
    });
</script>

<?php
// Get the captured content and store it in a variable
$content = ob_get_clean();

// Now include the sidenav layout which will wrap this content
require_once '../includes/sidenav.php';
?>