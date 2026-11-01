<?php require_once __DIR__ . '/../../config/SessionHandler.php'; AppSessionHandler::startSession(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relief Goods Management - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/dataTables.bootstrap5.min.css">
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

    $goods_model = new ReliefGoods();
    $goods_list = $goods_model->getAllGoods();
    $category_summary = $goods_model->getCategorySummary();
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
        <?php if (isset($_GET['action']) && $_GET['action'] == 'add'): ?>
        <?php
        $categories = $goods_model->getAllCategories();
        ?>
        <div class="page-title" style="margin-bottom: 30px;">
            <i class="bi bi-plus-circle"></i> Add New Relief Goods
        </div>

        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-box-seam"></i> Relief Goods Information
            </div>
            <div class="card-body">
                <form action="<?= APP_BASE_PATH ?>/controllers/ReliefGoodsController.php" method="POST">
                    <?= AppSessionHandler::csrfField() ?>
                    <input type="hidden" name="action" value="add">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>">
                                        <?php echo $cat['category_name']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Goods Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="goods_name" required placeholder="Enter goods name">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Enter description"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Unit of Measure</label>
                                <select class="form-select" name="unit_of_measure">
                                    <option value="piece">Piece</option>
                                    <option value="kg">Kilogram</option>
                                    <option value="liter">Liter</option>
                                    <option value="pack">Pack</option>
                                    <option value="box">Box</option>
                                    <option value="bundle">Bundle</option>
                                    <option value="set">Set</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Quantity in Stock</label>
                                <input type="number" class="form-control" name="quantity_in_stock" value="0" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Reorder Level</label>
                                <input type="number" class="form-control" name="reorder_level" value="10" min="0">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Expiration Date</label>
                                <input type="date" class="form-control" name="expiration_date">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Source</label>
                                <input type="text" class="form-control" name="source" placeholder="e.g., LGU, Donation">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Unit Cost (₱)</label>
                                <input type="number" step="0.01" class="form-control" name="unit_cost" value="0.00" min="0">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="relief-goods.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Save Relief Goods
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php else: ?>
        <div class="page-title" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <i class="bi bi-box-seam"></i> Relief Goods Inventory
            </div>
            <div>
                <a href="relief-goods.php?action=add" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> Add Relief Goods
                </a>
            </div>
        </div>

        <?php
        $low_stock_items = $goods_model->getLowStockItems();
        $expiring_items = $goods_model->getExpiringItems(30);
        ?>

        <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" href="#inventory" data-bs-toggle="tab">Inventory</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#low-stock" data-bs-toggle="tab">
                    Low Stock
                    <?php if (!empty($low_stock_items)): ?>
                        <span class="badge bg-danger"><?php echo count($low_stock_items); ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#expiring" data-bs-toggle="tab">
                    Expiring Soon
                    <?php if (!empty($expiring_items)): ?>
                        <span class="badge bg-warning"><?php echo count($expiring_items); ?></span>
                    <?php endif; ?>
                </a>
            </li>
        </ul>

        <div class="tab-content">
            <div id="inventory" class="tab-pane fade show active">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-list"></i> Inventory List
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="goodsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Goods Name</th>
                                        <th>Category</th>
                                        <th>Stock</th>
                                        <th>Reorder Level</th>
                                        <th>Unit Cost</th>
                                        <th>Expiration</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($goods_list as $g): ?>
                                    <tr>
                                        <td><?php echo $g['goods_name']; ?></td>
                                        <td><span class="badge bg-info"><?php echo $g['category_name']; ?></span></td>
                                        <td><strong><?php echo $g['quantity_in_stock']; ?></strong></td>
                                        <td><?php echo $g['reorder_level']; ?></td>
                                        <td>₱<?php echo number_format($g['unit_cost'], 2); ?></td>
                                        <td><?php echo $g['expiration_date'] ?? 'N/A'; ?></td>
                                        <td>
                                            <?php if ($g['quantity_in_stock'] <= $g['reorder_level']): ?>
                                                <span class="badge bg-danger">Low Stock</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">OK</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="goods-detail.php?id=<?php echo $g['id']; ?>" class="btn btn-sm btn-info">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="goods-edit.php?id=<?php echo $g['id']; ?>" class="btn btn-sm btn-warning">
                                                <i class="bi bi-pencil"></i>
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

            <div id="low-stock" class="tab-pane fade">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <i class="bi bi-exclamation-triangle"></i> Low Stock Items
                    </div>
                    <div class="card-body">
                        <?php if (empty($low_stock_items)): ?>
                            <p class="text-muted text-center">No low stock items. All items are above reorder level.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Goods Name</th>
                                            <th>Category</th>
                                            <th>Current Stock</th>
                                            <th>Reorder Level</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($low_stock_items as $item): ?>
                                        <tr>
                                            <td><?php echo $item['goods_name']; ?></td>
                                            <td><span class="badge bg-info"><?php echo $item['category_name']; ?></span></td>
                                            <td><strong class="text-danger"><?php echo $item['quantity_in_stock']; ?></strong></td>
                                            <td><?php echo $item['reorder_level']; ?></td>
                                            <td>
                                                <?php if ($item['quantity_in_stock'] == 0): ?>
                                                    <span class="badge bg-danger">Out of Stock</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Low Stock</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="goods-detail.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                                <a href="goods-edit.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-warning">
                                                    <i class="bi bi-pencil"></i> Restock
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

            <div id="expiring" class="tab-pane fade">
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <i class="bi bi-calendar-x"></i> Expiring Soon (Next 30 Days)
                    </div>
                    <div class="card-body">
                        <?php if (empty($expiring_items)): ?>
                            <p class="text-muted text-center">No items expiring in the next 30 days.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Goods Name</th>
                                            <th>Category</th>
                                            <th>Stock</th>
                                            <th>Expiration Date</th>
                                            <th>Days Left</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($expiring_items as $item): ?>
                                        <?php
                                        $expire_date = new DateTime($item['expiration_date']);
                                        $today = new DateTime();
                                        $days_left = $today->diff($expire_date)->days;
                                        if ($expire_date < $today) $days_left = -$days_left;
                                        ?>
                                        <tr>
                                            <td><?php echo $item['goods_name']; ?></td>
                                            <td><span class="badge bg-info"><?php echo $item['category_name']; ?></span></td>
                                            <td><?php echo $item['quantity_in_stock']; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($item['expiration_date'])); ?></td>
                                            <td>
                                                <?php if ($days_left < 0): ?>
                                                    <span class="badge bg-danger">Expired</span>
                                                <?php elseif ($days_left <= 7): ?>
                                                    <span class="badge bg-danger"><?php echo $days_left; ?> days</span>
                                                <?php elseif ($days_left <= 14): ?>
                                                    <span class="badge bg-warning"><?php echo $days_left; ?> days</span>
                                                <?php else: ?>
                                                    <span class="badge bg-info"><?php echo $days_left; ?> days</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="goods-detail.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="bi bi-eye"></i> View
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
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#goodsTable').DataTable({
                "pageLength": 10
            });
        });
    </script>
</body>
</html>
