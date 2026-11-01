<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Distribution Receipt - Admin</title>
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
        .receipt {
            max-width: 700px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
    <link rel="stylesheet" href="../../assets/css/app.css">
</head>
<body>
    <?php
    require_once __DIR__ . '/../../config/SessionHandler.php';
    AppSessionHandler::requireAdmin();

    require_once __DIR__ . '/../../models/Distribution.php';

    $distribution = new Distribution();
    $id = $_GET['id'] ?? 0;
    $dist = $distribution->getDistributionWithDetails($id);
    $details = $distribution->getDistributionDetails($id);

    if (!$dist) {
        header('Location: ' . APP_BASE_PATH . '/views/admin/distributions.php');
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
        <div class="receipt">
            <div class="text-center mb-4">
                <h3><i class="bi bi-box-seam"></i> <span class="brand-wordmark">Relief<span class="brand-accent">Link</span></span></h3>
                <h4 class="text-primary">OFFICIAL RECEIPT</h4>
                <hr>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <h6>Recipient Information</h6>
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td><strong>Name:</strong></td>
                            <td><?php echo $dist['household_name']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Household ID:</strong></td>
                            <td><?php echo $dist['household_id']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Address:</strong></td>
                            <td><?php echo $dist['barangay'] . ', ' . $dist['city']; ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6>Distribution Details</h6>
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td><strong>Batch Code:</strong></td>
                            <td><?php echo $dist['batch_code']; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Date:</strong></td>
                            <td><?php echo date('M d, Y h:i A', strtotime($dist['distribution_date'])); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Received By:</strong></td>
                            <td><?php echo $dist['staff_first'] . ' ' . $dist['staff_last']; ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-list"></i> Items Distributed
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Item</th>
                                <th>Category</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($details as $index => $detail): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo $detail['goods_name']; ?></td>
                                <td><?php echo $detail['category_name']; ?></td>
                                <td><strong><?php echo $detail['quantity_distributed']; ?></strong></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <p><strong>Notes:</strong></p>
                    <p><?php echo $dist['notes'] ?: 'No additional notes'; ?></p>
                </div>
                <div class="col-md-6 text-end">
                    <p><strong>Total Items:</strong> <?php echo count($details); ?></p>
                    <p class="text-muted">Receipt generated on <?php echo date('M d, Y h:i A'); ?></p>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4 no-print">
                <a href="distribution-detail.php?id=<?php echo $dist['batch_id']; ?>" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Batch
                </a>
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="bi bi-printer"></i> Print Receipt
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
