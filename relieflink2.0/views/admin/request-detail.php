<?php require_once __DIR__ . '/../../config/SessionHandler.php'; AppSessionHandler::startSession(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Detail - Admin</title>
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
    </style>
    <link rel="stylesheet" href="../../assets/css/app.css">
</head>
<body>
    <?php
    require_once __DIR__ . '/../../config/SessionHandler.php';
    AppSessionHandler::requireAdmin();

    require_once __DIR__ . '/../../models/ReliefRequest.php';

    $request_model = new ReliefRequest();
    $id = $_GET['id'] ?? 0;
    $request = $request_model->getRequestById($id);
    $items = $request_model->getRequestItems($id);

    if (!$request) {
        header('Location: ' . APP_BASE_PATH . '/views/admin/requests.php');
        exit;
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
        <div class="page-title" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <i class="bi bi-chat-dots"></i> Request #<?php echo $request['id']; ?>
            </div>
            <div>
                <a href="requests.php" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-info-circle"></i> Request Information
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Household:</strong></td>
                                <td><?php echo $request['household_name']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Household ID:</strong></td>
                                <td><code><?php echo $request['household_id']; ?></code></td>
                            </tr>
                            <tr>
                                <td><strong>Request Date:</strong></td>
                                <td><?php echo date('M d, Y h:i A', strtotime($request['request_date'])); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    <?php if ($request['status'] == 'Pending'): ?>
                                        <span class="badge bg-warning">Pending</span>
                                    <?php elseif ($request['status'] == 'Approved'): ?>
                                        <span class="badge bg-success">Approved</span>
                                    <?php elseif ($request['status'] == 'Rejected'): ?>
                                        <span class="badge bg-danger">Rejected</span>
                                    <?php else: ?>
                                        <span class="badge bg-info"><?php echo $request['status']; ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php if ($request['reviewed_at']): ?>
                            <tr>
                                <td><strong>Reviewed At:</strong></td>
                                <td><?php echo date('M d, Y h:i A', strtotime($request['reviewed_at'])); ?></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($request['notes']): ?>
                            <tr>
                                <td><strong>Notes:</strong></td>
                                <td><?php echo $request['notes']; ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <i class="bi bi-list"></i> Requested Items
                    </div>
                    <div class="card-body">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th>Category</th>
                                    <th>Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                <tr>
                                    <td><?php echo $item['goods_name']; ?></td>
                                    <td><?php echo $item['category_name']; ?></td>
                                    <td><?php echo $item['quantity_requested']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <i class="bi bi-chat-left-text"></i> Reason for Request
                    </div>
                    <div class="card-body">
                        <p><?php echo $request['request_reason']; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($request['status'] == 'Pending'): ?>
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <i class="bi bi-check-circle"></i> Actions
                    </div>
                    <div class="card-body">
                        <form action="<?= APP_BASE_PATH ?>/controllers/RequestController.php" method="POST" class="d-inline">
                            <?= AppSessionHandler::csrfField() ?>
                            <input type="hidden" name="action" value="approve">
                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                            <button type="submit" class="btn btn-success me-2" onclick="return confirm('Are you sure you want to approve this request?')">
                                <i class="bi bi-check-circle"></i> Approve Request
                            </button>
                        </form>
                        <form action="<?= APP_BASE_PATH ?>/controllers/RequestController.php" method="POST" class="d-inline">
                            <?= AppSessionHandler::csrfField() ?>
                            <input type="hidden" name="action" value="reject">
                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to reject this request?')">
                                <i class="bi bi-x-circle"></i> Reject Request
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
