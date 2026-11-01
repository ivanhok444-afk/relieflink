<?php require_once __DIR__ . '/../../config/SessionHandler.php'; AppSessionHandler::startSession(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Relief Goods - Admin</title>
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

    if (!$item) {
        header('Location: ' . APP_BASE_PATH . '/views/admin/relief-goods.php');
        exit;
    }

    $categories = $goods->getAllCategories();
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
            <i class="bi bi-pencil"></i> Edit Relief Goods
        </div>

        <div class="card">
            <div class="card-header bg-warning text-white">
                <i class="bi bi-box-seam"></i> Edit Relief Goods Information
            </div>
            <div class="card-body">
                <form action="<?= APP_BASE_PATH ?>/controllers/ReliefGoodsController.php" method="POST">
                    <?= AppSessionHandler::csrfField() ?>
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?php echo $item['id']; ?>">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo $cat['id'] == $item['category_id'] ? 'selected' : ''; ?>>
                                        <?php echo $cat['category_name']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Goods Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="goods_name" required value="<?php echo $item['goods_name']; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"><?php echo $item['description']; ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Unit of Measure</label>
                                <select class="form-select" name="unit_of_measure">
                                    <option value="piece" <?php echo $item['unit_of_measure'] == 'piece' ? 'selected' : ''; ?>>Piece</option>
                                    <option value="kg" <?php echo $item['unit_of_measure'] == 'kg' ? 'selected' : ''; ?>>Kilogram</option>
                                    <option value="liter" <?php echo $item['unit_of_measure'] == 'liter' ? 'selected' : ''; ?>>Liter</option>
                                    <option value="pack" <?php echo $item['unit_of_measure'] == 'pack' ? 'selected' : ''; ?>>Pack</option>
                                    <option value="box" <?php echo $item['unit_of_measure'] == 'box' ? 'selected' : ''; ?>>Box</option>
                                    <option value="bundle" <?php echo $item['unit_of_measure'] == 'bundle' ? 'selected' : ''; ?>>Bundle</option>
                                    <option value="set" <?php echo $item['unit_of_measure'] == 'set' ? 'selected' : ''; ?>>Set</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Quantity in Stock</label>
                                <input type="number" class="form-control" name="quantity_in_stock" value="<?php echo $item['quantity_in_stock']; ?>" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Reorder Level</label>
                                <input type="number" class="form-control" name="reorder_level" value="<?php echo $item['reorder_level']; ?>" min="0">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Expiration Date</label>
                                <input type="date" class="form-control" name="expiration_date" value="<?php echo $item['expiration_date'] ?? ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Source</label>
                                <input type="text" class="form-control" name="source" value="<?php echo $item['source']; ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Unit Cost (₱)</label>
                                <input type="number" step="0.01" class="form-control" name="unit_cost" value="<?php echo $item['unit_cost']; ?>" min="0">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <div>
                            <a href="relief-goods.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Back to List
                            </a>
                            <button type="button" class="btn btn-danger" onclick="deleteGoods(<?php echo $item['id']; ?>)">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </div>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-check-circle"></i> Update Relief Goods
                        </button>
                    </div>
                </form>

                <form id="deleteForm" action="<?= APP_BASE_PATH ?>/controllers/ReliefGoodsController.php" method="POST" style="display: none;">
                    <?= AppSessionHandler::csrfField() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="deleteId">
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function deleteGoods(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('deleteId').value = id;
                    document.getElementById('deleteForm').submit();
                }
            });
        }
    </script>
</body>
</html>
