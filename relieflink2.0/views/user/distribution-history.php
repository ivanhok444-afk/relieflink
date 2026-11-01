<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Distribution History - ReliefLink</title>
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
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
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
    </style>
    <link rel="stylesheet" href="../../assets/css/app.css">
</head>
<body>
    <?php
    require_once __DIR__ . '/../../config/SessionHandler.php';
    AppSessionHandler::requireLogin();

    require_once __DIR__ . '/../../models/Household.php';
    require_once __DIR__ . '/../../models/Distribution.php';

    $household = new Household();
    $distribution = new Distribution();

    $user_id = AppSessionHandler::getUserId();
    $user_household = $household->getHouseholdByUserId($user_id);
    $history = [];

    if ($user_household) {
        $history = $distribution->getDistributionHistory($user_household['id']);
    }
    ?>

    <!-- Navbar -->
    <nav class="navbar navbar-dark navbar-expand-lg fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= APP_BASE_PATH ?>/views/user/dashboard.php">
                <i class="bi bi-box-seam"></i> <span class="brand-wordmark">Relief<span class="brand-accent">Link</span></span>
            </a>
            <div class="ms-auto">
                <span class="navbar-text me-3">Welcome, <strong><?php echo $_SESSION['username']; ?></strong></span>
                <a class="btn btn-sm btn-light" href="<?= APP_BASE_PATH ?>/controllers/LogoutController.php" data-swal-logout>
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar">
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-link">
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
            <a href="distribution-history.php" class="nav-link active">
                <i class="bi bi-clock-history"></i> Distribution History
            </a>
            <a href="profile.php" class="nav-link">
                <i class="bi bi-person-circle"></i> My Profile
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-title" style="margin-bottom: 30px;">
            <i class="bi bi-clock-history"></i> Distribution History
        </div>

        <?php if (!$user_household): ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> You don't have a household registered yet. Please contact the administrator.
            </div>
        <?php elseif (empty($history)): ?>
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-list"></i> Previous Distributions
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> You have not received any distributions yet.
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header bg-success text-white">
                    <i class="bi bi-box-arrow-in-down"></i> How Distribution Works
                </div>
                <div class="card-body">
                    <ol>
                        <li>Submit a relief request through the "Relief Request" section</li>
                        <li>Wait for the administrator to review and approve your request</li>
                        <li>Once approved, you will receive a notification</li>
                        <li>Attend the distribution event with your household ID or QR code</li>
                        <li>Receive your approved relief items</li>
                    </ol>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-list"></i> Previous Distributions
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Batch Code</th>
                                    <th>Date</th>
                                    <th>Items Received</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($history as $dist): ?>
                                <tr>
                                    <td><code><?php echo $dist['batch_code']; ?></code></td>
                                    <td><?php echo date('M d, Y', strtotime($dist['distribution_date'])); ?></td>
                                    <td><?php echo date('h:i A', strtotime($dist['distribution_date'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/sweetalert2.all.min.js"></script>
    <script src="../../assets/js/user-actions.js"></script>
</body>
</html>
