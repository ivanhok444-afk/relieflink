<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
        }
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        }
        .sidebar {
            background: white;
            position: fixed;
            width: 250px;
            left: 0;
            top: 56px;
            min-height: 100vh;
        }
        .sidebar-nav .nav-link.active {
            background-color: #f0f4ff;
            border-left-color: var(--primary);
            color: var(--primary);
            font-weight: 600;
        }
        .main-content {
            margin-left: 250px;
            margin-top: 56px;
            padding: 30px 20px;
        }
        .report-card {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .report-card:hover {
            border-color: var(--primary);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .report-icon {
            font-size: 40px;
            color: var(--primary);
            margin-bottom: 10px;
        }
    </style>
    <link rel="stylesheet" href="../../assets/css/app.css">
</head>
<body>
    <?php
    require_once __DIR__ . '/../../config/SessionHandler.php';
    AppSessionHandler::requireAdmin();
    ?>

    <!-- Navbar -->
    <nav class="navbar navbar-dark navbar-expand-lg fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= APP_BASE_PATH ?>/views/admin/dashboard.php">
                <i class="bi bi-box-seam"></i> <span class="brand-wordmark">Relief<span class="brand-accent">Link</span></span>
            </a>
            <div class="ms-auto">
                <span class="navbar-text me-3">Welcome, <strong><?php echo $_SESSION['username']; ?></strong></span>
                <a class="btn btn-sm btn-light" href="<?= APP_BASE_PATH ?>/controllers/LogoutController.php">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <?php require __DIR__ . '/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-title" style="margin-bottom: 30px;">
            <i class="bi bi-file-earmark-text"></i> Reports
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="report-card" onclick="window.location='report-distribution.php'">
                    <div class="report-icon">
                        <i class="bi bi-box-arrow-in-down"></i>
                    </div>
                    <h5>Distribution Report</h5>
                    <p class="text-muted">View all distribution activities and batches</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="report-card" onclick="window.location='report-inventory.php'">
                    <div class="report-icon">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <h5>Inventory Report</h5>
                    <p class="text-muted">Current stock levels and inventory status</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="report-card" onclick="window.location='report-household.php'">
                    <div class="report-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <h5>Household Report</h5>
                    <p class="text-muted">Household details and eligibility status</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="report-card" onclick="window.location='report-requests.php'">
                    <div class="report-icon">
                        <i class="bi bi-chat-dots"></i>
                    </div>
                    <h5>Request Report</h5>
                    <p class="text-muted">All relief requests and their status</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="report-card" onclick="window.location='report-activity.php'">
                    <div class="report-icon">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <h5>Activity Report</h5>
                    <p class="text-muted">User activities and system logs</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="report-card" onclick="window.location='report-beneficiaries.php'">
                    <div class="report-icon">
                        <i class="bi bi-percent"></i>
                    </div>
                    <h5>Beneficiaries Report</h5>
                    <p class="text-muted">Beneficiary statistics and distribution history</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
