<?php require_once __DIR__ . '/../../config/SessionHandler.php'; AppSessionHandler::startSession(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relief Request - ReliefLink</title>
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
    require_once __DIR__ . '/../../config/config.php';
    AppSessionHandler::requireLogin();

    require_once __DIR__ . '/../../models/Household.php';
    require_once __DIR__ . '/../../models/ReliefGoods.php';
    require_once __DIR__ . '/../../models/ReliefRequest.php';

    $household_model = new Household();
    $goods_model = new ReliefGoods();
    $request_model = new ReliefRequest();

    $user_id = AppSessionHandler::getUserId();
    $user_household = $household_model->getHouseholdByUserId($user_id);
    $available_goods = $goods_model->getAllGoods();
    $my_requests = $request_model->getHouseholdRequests($user_household['id'] ?? 0);
    ?>

    <!-- Navbar -->
    <nav class="navbar navbar-dark navbar-expand-lg fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= APP_BASE_PATH ?>/views/user/dashboard.php">
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

    <!-- Sidebar -->
    <div class="sidebar">
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-link">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="my-household.php" class="nav-link">
                <i class="bi bi-person-fill"></i> My Household
            </a>
            <a href="relief-request.php" class="nav-link active">
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
        <div class="page-title" style="margin-bottom: 30px;">
            <i class="bi bi-chat-dots"></i> Submit Relief Request
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" href="#new-request" data-bs-toggle="tab">Create New Request</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#my-requests" data-bs-toggle="tab">My Requests</a>
            </li>
        </ul>

        <div class="tab-content">
            <!-- New Request Tab -->
            <div id="new-request" class="tab-pane fade show active">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-plus-circle"></i> New Relief Request
                    </div>
                    <div class="card-body">
                        <?php if (!$user_household): ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i> You need to have a household registered before submitting a request. Please contact the administrator.
                            </div>
                        <?php else: ?>
                        <form action="<?= APP_BASE_PATH ?>/controllers/ReliefRequestController.php" method="POST" data-goods-limit="<?php echo MAX_GOODS_PER_REQUEST; ?>" data-swal-confirm="Submit this relief request?" data-swal-text="Your request will be sent to the administrator for review." data-swal-confirm-button="Submit request">
                            <?= AppSessionHandler::csrfField() ?>
                            <div class="mb-3">
                                <label class="form-label">Request Reason <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="request_reason" rows="4" placeholder="Please describe why you need relief assistance..." required></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Select Relief Items <span class="text-danger">*</span></label>
                                <div class="d-flex justify-content-between small text-muted mb-2">
                                    <span>Select up to <?php echo MAX_GOODS_PER_REQUEST; ?> different goods. Quantities are counted per item.</span>
                                    <span data-goods-count aria-live="polite">0 / <?php echo MAX_GOODS_PER_REQUEST; ?> selected</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Item</th>
                                                <th>Category</th>
                                                <th>Available Stock</th>
                                                <th>Quantity Requested</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($available_goods as $item): ?>
                                            <tr>
                                                <td><strong><?php echo $item['goods_name']; ?></strong></td>
                                                <td><span class="badge bg-info"><?php echo $item['category_name']; ?></span></td>
                                                <td><?php echo $item['quantity_in_stock']; ?> <?php echo $item['unit_of_measure']; ?></td>
                                                <td style="max-width: 200px;">
                                                    <input type="number" class="form-control" name="items[<?php echo $item['id']; ?>]" min="0" max="<?php echo $item['quantity_in_stock']; ?>" value="0">
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> Your request will be reviewed by the administrator. You will be notified once it is approved or rejected.
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Submit Request
                            </button>
                            <a href="dashboard.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- My Requests Tab -->
            <div id="my-requests" class="tab-pane fade">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-list"></i> Your Relief Requests
                    </div>
                    <div class="card-body">
                        <?php if (empty($my_requests)): ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> You have no relief requests yet. Click the "Create New Request" tab to submit your first request.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Reason</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($my_requests as $req): ?>
                                        <tr>
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
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/sweetalert2.all.min.js"></script>
    <script src="../../assets/js/user-actions.js"></script>
    <script src="../../assets/js/goods-limit.js"></script>
</body>
</html>
