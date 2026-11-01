<?php require_once __DIR__ . '/../../config/SessionHandler.php'; AppSessionHandler::startSession(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Distribute Goods - Admin</title>
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
    require_once __DIR__ . '/../../config/config.php';
    AppSessionHandler::requireAdmin();

    require_once __DIR__ . '/../../models/Distribution.php';
    require_once __DIR__ . '/../../models/Household.php';
    require_once __DIR__ . '/../../models/ReliefGoods.php';

    $distribution = new Distribution();
    $household = new Household();
    $goods = new ReliefGoods();

    $batch_id = $_GET['batch_id'] ?? 0;
    $household_id = $_GET['household_id'] ?? 0;

    $batch = $distribution->getBatchById($batch_id);
    $household_data = $household->getHouseholdById($household_id);
    $available_goods = $goods->getAvailableGoodsForDistribution();
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
            <i class="bi bi-box-seam"></i> Distribute Goods
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <i class="bi bi-house-door"></i> Recipient
                    </div>
                    <div class="card-body">
                        <h5><?php echo $household_data['household_name']; ?></h5>
                        <p class="text-muted mb-0"><?php echo $household_data['household_id']; ?> | <?php echo $household_data['barangay'] . ', ' . $household_data['city']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <i class="bi bi-calendar"></i> Batch
                    </div>
                    <div class="card-body">
                        <h5><?php echo $batch['batch_code']; ?></h5>
                        <p class="text-muted mb-0"><?php echo date('M d, Y', strtotime($batch['batch_date'])); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-warning text-white">
                <i class="bi bi-list-check"></i> Select Items to Distribute
            </div>
            <div class="card-body">
                <form action="<?= APP_BASE_PATH ?>/controllers/DistributionController.php" method="POST" id="distributeForm" data-goods-limit="<?php echo MAX_GOODS_PER_REQUEST; ?>">
                    <?= AppSessionHandler::csrfField() ?>
                    <input type="hidden" name="action" value="distribute">
                    <input type="hidden" name="batch_id" value="<?php echo $batch_id; ?>">
                    <input type="hidden" name="household_id" value="<?php echo $household_id; ?>">
                    <div class="d-flex justify-content-between small text-muted mb-2">
                        <span>Choose up to <?php echo MAX_GOODS_PER_REQUEST; ?> different goods for this household.</span>
                        <span data-goods-count aria-live="polite">0 / <?php echo MAX_GOODS_PER_REQUEST; ?> selected</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th>Category</th>
                                    <th>Available Stock</th>
                                    <th>Quantity to Distribute</th>
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

                    <div class="d-flex justify-content-between mt-4">
                        <a href="distribution-eligibility.php?batch_id=<?php echo $batch_id; ?>&household_id=<?php echo $household_id; ?>" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-check-circle"></i> Confirm Distribution
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/goods-limit.js"></script>
</body>
</html>
