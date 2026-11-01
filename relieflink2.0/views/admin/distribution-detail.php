<?php require_once __DIR__ . '/../../config/SessionHandler.php'; AppSessionHandler::startSession(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Batch Detail - Admin</title>
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

    require_once __DIR__ . '/../../models/Distribution.php';

    $distribution = new Distribution();
    $id = $_GET['id'] ?? 0;
    $batch = $distribution->getBatchById($id);
    $batch_distributions = $distribution->getBatchDistributions($id);

    if (!$batch) {
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
        <div class="page-title" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <i class="bi bi-calendar"></i> Batch: <?php echo $batch['batch_code']; ?>
            </div>
            <div>
                <?php if ($batch['status'] == 'Pending'): ?>
                    <a href="distribution-scan.php?batch_id=<?php echo $batch['id']; ?>" class="btn btn-success btn-sm me-2">
                        <i class="bi bi-qr-code-scan"></i> Scan QR
                    </a>
                    <form action="<?= APP_BASE_PATH ?>/controllers/DistributionController.php" method="POST" style="display: inline;" id="completeForm">
                        <?= AppSessionHandler::csrfField() ?>
                        <input type="hidden" name="action" value="complete_batch">
                        <input type="hidden" name="batch_id" value="<?php echo $batch['id']; ?>">
                        <button type="button" class="btn btn-primary btn-sm me-2" onclick="confirmAction('Mark this batch as completed?', 'This action cannot be undone.', function(){ document.getElementById('completeForm').submit(); })">
                            <i class="bi bi-check-circle"></i> Complete
                        </button>
                    </form>
                    <form action="<?= APP_BASE_PATH ?>/controllers/DistributionController.php" method="POST" style="display: inline;" id="cancelForm">
                        <?= AppSessionHandler::csrfField() ?>
                        <input type="hidden" name="action" value="cancel_batch">
                        <input type="hidden" name="batch_id" value="<?php echo $batch['id']; ?>">
                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmAction('Cancel this batch?', 'This action cannot be undone.', function(){ document.getElementById('cancelForm').submit(); })">
                            <i class="bi bi-x-circle"></i> Cancel
                        </button>
                    </form>
                <?php endif; ?>
                <a href="distributions.php" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Batch Code</h6>
                        <h4><code><?php echo $batch['batch_code']; ?></code></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Date</h6>
                        <h4><?php echo date('M d, Y', strtotime($batch['batch_date'])); ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Status</h6>
                        <h4>
                            <?php if ($batch['status'] == 'Pending'): ?>
                                <span class="badge bg-warning">Pending</span>
                            <?php elseif ($batch['status'] == 'In Progress'): ?>
                                <span class="badge bg-info">In Progress</span>
                            <?php elseif ($batch['status'] == 'Completed'): ?>
                                <span class="badge bg-success">Completed</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Cancelled</span>
                            <?php endif; ?>
                        </h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <h6 class="text-muted">Recipients</h6>
                        <h4><?php echo count($batch_distributions); ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-people"></i> Recipients (<?php echo count($batch_distributions); ?>)
            </div>
            <div class="card-body">
                <?php if (empty($batch_distributions)): ?>
                    <p class="text-muted text-center">No distributions yet. Scan QR codes to distribute goods.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Household</th>
                                    <th>Location</th>
                                    <th>Date & Time</th>
                                    <th>Distributed By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($batch_distributions as $dist): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo $dist['household_name']; ?></strong><br>
                                        <small class="text-muted"><?php echo $dist['household_id']; ?></small>
                                    </td>
                                    <td><?php echo $dist['barangay'] . ', ' . $dist['city']; ?></td>
                                    <td><?php echo date('M d, Y h:i A', strtotime($dist['distribution_date'])); ?></td>
                                    <td><?php echo $dist['first_name'] . ' ' . $dist['last_name']; ?></td>
                                    <td>
                                        <a href="distribution-receipt.php?id=<?php echo $dist['id']; ?>" class="btn btn-sm btn-info">
                                            <i class="bi bi-receipt"></i> Receipt
                                        </a>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmAction(title, text, callback) {
            Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, proceed!'
            }).then((result) => {
                if (result.isConfirmed) {
                    callback();
                }
            });
            return false;
        }
    </script>
</body>
</html>
