<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Eligibility - Admin</title>
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
        .sidebar-nav .nav-link {
            color: #333;
            padding: 12px 20px;
            border-left: 4px solid transparent;
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
        .status-badge {
            font-size: 18px;
            padding: 10px 20px;
        }
    </style>
    <link rel="stylesheet" href="../../assets/css/app.css">
</head>
<body>
    <?php
    require_once __DIR__ . '/../../config/SessionHandler.php';
    AppSessionHandler::requireAdmin();

    require_once __DIR__ . '/../../models/Distribution.php';
    require_once __DIR__ . '/../../models/Household.php';
    require_once __DIR__ . '/../../models/ReliefRequest.php';

    $distribution = new Distribution();
    $household = new Household();
    $request_model = new ReliefRequest();

    $batch_id = $_GET['batch_id'] ?? 0;
    $household_id = $_GET['household_id'] ?? 0;

    $batch = $distribution->getBatchById($batch_id);
    $household_data = $household->getHouseholdById($household_id);
    $already_received = $distribution->hasReceivedFromBatch($household_id, $batch_id);
    $is_eligible = $household_data['is_eligible'] ?? false;
    $pending_requests = $request_model->getHouseholdRequests($household_id);
    $has_approved = false;
    foreach ($pending_requests as $req) {
        if ($req['status'] == 'Approved') {
            $has_approved = true;
            break;
        }
    }
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
            <i class="bi bi-person-check"></i> Eligibility Check
        </div>

        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-house-door"></i> Household Information
                    </div>
                    <div class="card-body">
                        <h4><?php echo $household_data['household_name']; ?></h4>
                        <p class="text-muted">ID: <?php echo $household_data['household_id']; ?></p>
                        <p><strong>Address:</strong> <?php echo $household_data['barangay'] . ', ' . $household_data['city']; ?></p>
                        <p><strong>Contact:</strong> <?php echo $household_data['contact_number']; ?></p>
                        <p><strong>Members:</strong> <?php echo $household_data['total_members']; ?></p>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header bg-info text-white">
                        <i class="bi bi-clipboard-check"></i> Eligibility Status
                    </div>
                    <div class="card-body">
                        <?php if ($already_received): ?>
                            <div class="alert alert-danger">
                                <h5><i class="bi bi-x-circle"></i> Already Received</h5>
                                <p>This household has already received relief goods from this batch.</p>
                            </div>
                        <?php elseif (!$is_eligible): ?>
                            <div class="alert alert-warning">
                                <h5><i class="bi bi-exclamation-triangle"></i> Not Eligible</h5>
                                <p>This household is currently marked as ineligible.</p>
                                <p><strong>Reason:</strong> <?php echo $household_data['eligibility_reason'] ?? 'Not specified'; ?></p>
                            </div>
                        <?php elseif (!$has_approved): ?>
                            <div class="alert alert-warning">
                                <h5><i class="bi bi-exclamation-triangle"></i> No Approved Request</h5>
                                <p>This household does not have an approved relief request for this batch.</p>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-success">
                                <h5><i class="bi bi-check-circle"></i> Eligible for Distribution</h5>
                                <p>This household is eligible to receive relief goods.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="distribution-scan.php?batch_id=<?php echo $batch_id; ?>" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Scan Another
                    </a>

                    <?php if (!$already_received && $is_eligible && $has_approved): ?>
                        <a href="distribution-distribute.php?batch_id=<?php echo $batch_id; ?>&household_id=<?php echo $household_id; ?>" class="btn btn-success btn-lg">
                            <i class="bi bi-check-circle"></i> Proceed to Distribution
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
