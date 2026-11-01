<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Report - Admin</title>
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
    AppSessionHandler::requireAdmin();

    require_once __DIR__ . '/../../models/ReliefRequest.php';

    $request_model = new ReliefRequest();
    $all_requests = $request_model->getAllRequests();
    $pending = $request_model->getRequestsByStatus('Pending');
    $approved = $request_model->getRequestsByStatus('Approved');
    $rejected = $request_model->getRequestsByStatus('Rejected');
    $distributed = $request_model->getRequestsByStatus('Distributed');
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
            <i class="bi bi-chat-dots"></i> Request Report
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Total Requests</h6>
                        <h2><?php echo count($all_requests); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Pending</h6>
                        <h2 class="text-warning"><?php echo count($pending); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Approved</h6>
                        <h2 class="text-success"><?php echo count($approved); ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Rejected</h6>
                        <h2 class="text-danger"><?php echo count($rejected); ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-list"></i> All Requests
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Household</th>
                                <th>Date</th>
                                <th>Reason</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_requests as $req): ?>
                            <tr>
                                <td><?php echo $req['household_name']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($req['request_date'])); ?></td>
                                <td><?php echo substr($req['request_reason'], 0, 50) . '...'; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $req['status'] == 'Approved' ? 'success' : ($req['status'] == 'Pending' ? 'warning' : ($req['status'] == 'Rejected' ? 'danger' : 'info')); ?>">
                                        <?php echo $req['status']; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <a href="reports.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Reports
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
