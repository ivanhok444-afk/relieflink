<?php require_once __DIR__ . '/../../config/SessionHandler.php'; AppSessionHandler::startSession(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goods Detail - Admin</title>
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

    require_once __DIR__ . '/../../models/ReliefGoods.php';

    $goods = new ReliefGoods();
    $id = $_GET['id'] ?? 0;
    $item = $goods->getGoodsById($id);
    $movements = $goods->getStockMovements($id);

    if (!$item) {
        header('Location: ' . APP_BASE_PATH . '/views/admin/relief-goods.php');
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
                <i class="bi bi-box-seam"></i> <?php echo $item['goods_name']; ?>
            </div>
            <div>
                <a href="goods-edit.php?id=<?php echo $item['id']; ?>" class="btn btn-warning btn-sm me-2">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <a href="relief-goods.php" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-info-circle"></i> Item Information
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Category:</strong></td>
                                <td><span class="badge bg-info"><?php echo $item['category_name']; ?></span></td>
                            </tr>
                            <tr>
                                <td><strong>Unit of Measure:</strong></td>
                                <td><?php echo ucfirst($item['unit_of_measure']); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Source:</strong></td>
                                <td><?php echo $item['source']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Unit Cost:</strong></td>
                                <td>₱<?php echo number_format($item['unit_cost'], 2); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    <?php if ($item['quantity_in_stock'] <= $item['reorder_level']): ?>
                                        <span class="badge bg-danger">Low Stock</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">In Stock</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header bg-success text-white">
                        <i class="bi bi-currency-dollar"></i> Stock Value
                    </div>
                    <div class="card-body">
                        <h4>₱<?php echo number_format($item['quantity_in_stock'] * $item['unit_cost'], 2); ?></h4>
                        <p class="text-muted">Total value of current stock</p>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <i class="bi bi-plus-circle"></i> Stock In
                            </div>
                            <div class="card-body">
                                <form action="<?= APP_BASE_PATH ?>/controllers/ReliefGoodsController.php" method="POST">
                                    <?= AppSessionHandler::csrfField() ?>
                                    <input type="hidden" name="action" value="stock_in">
                                    <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                    <div class="mb-3">
                                        <label class="form-label">Quantity</label>
                                        <input type="number" class="form-control" name="quantity" required min="1">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Notes</label>
                                        <input type="text" class="form-control" name="notes" placeholder="e.g., Received from LGU">
                                    </div>
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="bi bi-plus-circle"></i> Add Stock
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-warning text-white">
                                <i class="bi bi-dash-circle"></i> Stock Out
                            </div>
                            <div class="card-body">
                                <form action="<?= APP_BASE_PATH ?>/controllers/ReliefGoodsController.php" method="POST">
                                    <?= AppSessionHandler::csrfField() ?>
                                    <input type="hidden" name="action" value="stock_out">
                                    <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                    <div class="mb-3">
                                        <label class="form-label">Quantity</label>
                                        <input type="number" class="form-control" name="quantity" required min="1" max="<?php echo $item['quantity_in_stock']; ?>">
                                        <small class="text-muted">Available: <?php echo $item['quantity_in_stock']; ?></small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Notes</label>
                                        <input type="text" class="form-control" name="notes" placeholder="e.g., Damaged/Expired">
                                    </div>
                                    <button type="submit" class="btn btn-warning w-100">
                                        <i class="bi bi-dash-circle"></i> Remove Stock
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <i class="bi bi-clock-history"></i> Stock Movements
                    </div>
                    <div class="card-body">
                        <?php if (empty($movements)): ?>
                            <p class="text-muted text-center">No stock movements recorded yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Quantity</th>
                                            <th>Notes</th>
                                            <th>Created By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($movements as $m): ?>
                                        <tr>
                                            <td><?php echo date('M d, Y h:i A', strtotime($m['created_at'])); ?></td>
                                            <td>
                                                <?php if ($m['movement_type'] == 'Stock In'): ?>
                                                    <span class="badge bg-success">Stock In</span>
                                                <?php elseif ($m['movement_type'] == 'Stock Out'): ?>
                                                    <span class="badge bg-warning">Stock Out</span>
                                                <?php elseif ($m['movement_type'] == 'Distributed'): ?>
                                                    <span class="badge bg-info">Distributed</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Expired</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $m['quantity']; ?></td>
                                            <td><?php echo $m['notes'] ?: '-'; ?></td>
                                            <td><?php echo $m['created_by_name'] ?? 'System'; ?></td>
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
</body>
</html>
