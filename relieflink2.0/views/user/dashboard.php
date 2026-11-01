<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - ReliefLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
        }
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .sidebar {
            background: white;
            min-height: 100vh;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
            position: fixed;
            width: 250px;
            left: 0;
            top: 56px;
        }
        .sidebar-nav .nav-link {
            color: #333;
            padding: 12px 20px;
            border-left: 4px solid transparent;
            transition: all 0.3s;
        }
        .sidebar-nav .nav-link:hover {
            background-color: #f0f4ff;
            border-left-color: var(--primary);
            color: var(--primary);
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
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border-top: 4px solid;
        }
        .stat-card.household {
            border-top-color: #667eea;
        }
        .stat-card.distribution {
            border-top-color: #4facfe;
        }
        .stat-card.request {
            border-top-color: #fa709a;
        }
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #333;
        }
        .stat-label {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
        .page-title {
            color: #333;
            margin-bottom: 30px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .card {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border: none;
        }
    </style>
    <link rel="stylesheet" href="../../assets/css/app.css">
</head>
<body>
    <?php
    require_once __DIR__ . '/../../config/SessionHandler.php';
    AppSessionHandler::requireLogin();

    require_once __DIR__ . '/../../models/Household.php';
    require_once __DIR__ . '/../../models/Distribution.php';
    require_once __DIR__ . '/../../models/ReliefRequest.php';

    $user_id = AppSessionHandler::getUserId();

    $household = new Household();
    $distribution = new Distribution();
    $request_model = new ReliefRequest();
    ?>

    <!-- Navbar -->
    <nav class="navbar navbar-dark navbar-expand-lg fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= APP_BASE_PATH ?>/views/user/dashboard.php">
                <i class="bi bi-box-seam"></i> <span class="brand-wordmark">Relief<span class="brand-accent">Link</span></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="navbar-text me-3">Welcome, <strong><?php echo $_SESSION['username']; ?></strong></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= APP_BASE_PATH ?>/controllers/LogoutController.php" data-swal-logout>
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar">
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-link active">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="my-household.php" class="nav-link">
                <i class="bi bi-person-fill"></i> My Household
            </a>
            <a href="relief-request.php" class="nav-link">
                <i class="bi bi-chat-dots"></i> Relief Request
            </a>
            <a href="business-chat.php" class="nav-link">
                <i class="bi bi-chat-square-text"></i> Chat with Admin
            </a>
            <a href="distribution-history.php" class="nav-link">
                <i class="bi bi-clock-history"></i> Distribution History
            </a>
            <a href="profile.php" class="nav-link">
                <i class="bi bi-person-circle"></i> My Profile
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-title">
            <i class="bi bi-speedometer2"></i> Dashboard
        </div>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-6 col-lg-4">
                <div class="stat-card household">
                    <div class="stat-number">1</div>
                    <div class="stat-label">My Household</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="stat-card distribution">
                    <div class="stat-number">5</div>
                    <div class="stat-label">Total Distributions</div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="stat-card request">
                    <div class="stat-number">2</div>
                    <div class="stat-label">Active Requests</div>
                </div>
            </div>
        </div>

        <!-- Welcome Section -->
        <div class="card mt-4">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-info-circle"></i> Your Information
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Household Status</h6>
                        <p><span class="badge bg-success">Eligible for Relief Assistance</span></p>
                        <p class="text-muted">Your household is registered and eligible to receive relief goods.</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Quick Actions</h6>
                        <a href="relief-request.php" class="btn btn-sm btn-info me-2">
                            <i class="bi bi-plus-circle"></i> Submit Request
                        </a>
                        <a href="my-household.php" class="btn btn-sm btn-warning">
                            <i class="bi bi-pencil"></i> Edit Household
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Distributions -->
        <div class="card mt-4">
            <div class="card-header bg-success text-white">
                <i class="bi bi-box-arrow-in-down"></i> Recent Distributions
            </div>
            <div class="card-body">
                <p class="text-muted">No recent distributions. Check your distribution history for more details.</p>
                <a href="distribution-history.php" class="btn btn-sm btn-primary">
                    <i class="bi bi-eye"></i> View Full History
                </a>
            </div>
        </div>

        <!-- Latest Requests -->
        <div class="card mt-4">
            <div class="card-header bg-warning text-white">
                <i class="bi bi-chat-dots"></i> Your Latest Requests
            </div>
            <div class="card-body">
                <p class="text-muted">No active requests at this moment.</p>
                <a href="relief-request.php" class="btn btn-sm btn-warning">
                    <i class="bi bi-plus-circle"></i> Create New Request
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/sweetalert2.all.min.js"></script>
    <script src="../../assets/js/user-actions.js"></script>
</body>
</html>
