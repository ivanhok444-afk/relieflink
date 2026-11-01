<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relief Requests - Admin</title>
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
            <i class="bi bi-chat-dots"></i> Relief Requests
        </div>

        <!-- Request Tabs -->
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" href="#pending" data-bs-toggle="tab">Pending</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#approved" data-bs-toggle="tab">Approved</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#rejected" data-bs-toggle="tab">Rejected</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#all" data-bs-toggle="tab">All Requests</a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content">
            <div id="pending" class="tab-pane fade show active">
                <div class="card">
                    <div class="card-header bg-warning text-dark">Pending Requests</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Household</th>
                                        <th>Requested Date</th>
                                        <th>Reason</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($request_model->getRequestsByStatus('Pending') as $req): ?>
                                    <tr>
                                        <td><?php echo $req['household_name']; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($req['request_date'])); ?></td>
                                        <td><?php echo substr($req['request_reason'], 0, 50) . '...'; ?></td>
                                        <td><span class="badge bg-warning">Pending</span></td>
                                        <td>
                                            <a href="request-detail.php?id=<?php echo $req['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i> Review
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div id="approved" class="tab-pane fade">
                <div class="card">
                    <div class="card-header bg-success text-white">Approved Requests</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Household</th>
                                        <th>Approved Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($request_model->getRequestsByStatus('Approved') as $req): ?>
                                    <tr>
                                        <td><?php echo $req['household_name']; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($req['reviewed_at'])); ?></td>
                                        <td><span class="badge bg-success">Approved</span></td>
                                        <td>
                                            <a href="request-detail.php?id=<?php echo $req['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div id="rejected" class="tab-pane fade">
                <div class="card">
                    <div class="card-header bg-danger text-white">Rejected Requests</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Household</th>
                                        <th>Rejected Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($request_model->getRequestsByStatus('Rejected') as $req): ?>
                                    <tr>
                                        <td><?php echo $req['household_name']; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($req['reviewed_at'])); ?></td>
                                        <td><span class="badge bg-danger">Rejected</span></td>
                                        <td>
                                            <a href="request-detail.php?id=<?php echo $req['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div id="all" class="tab-pane fade">
                <div class="card">
                    <div class="card-header bg-primary text-white">All Requests</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Household</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($all_requests as $req): ?>
                                    <tr>
                                        <td><?php echo $req['household_name']; ?></td>
                                        <td><?php echo date('M d, Y', strtotime($req['request_date'])); ?></td>
                                        <td><span class="badge bg-info"><?php echo $req['status']; ?></span></td>
                                        <td>
                                            <a href="request-detail.php?id=<?php echo $req['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
