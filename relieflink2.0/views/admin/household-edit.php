<?php require_once __DIR__ . '/../../config/SessionHandler.php'; AppSessionHandler::startSession(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Household - Admin</title>
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

    require_once __DIR__ . '/../../models/Household.php';
    require_once __DIR__ . '/../../models/User.php';

    $household = new Household();
    $user_model = new User();
    $id = $_GET['id'] ?? 0;
    $household_data = $household->getHouseholdById($id);
    $users = $user_model->getAllUsers();

    if (!$household_data) {
        header('Location: ' . APP_BASE_PATH . '/views/admin/households.php');
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
        <div class="page-title" style="margin-bottom: 30px;">
            <i class="bi bi-pencil"></i> Edit Household
        </div>

        <div class="card">
            <div class="card-header bg-warning text-white">
                <i class="bi bi-house-door"></i> Edit Household Information
            </div>
            <div class="card-body">
                <form action="<?= APP_BASE_PATH ?>/controllers/HouseholdController.php" method="POST">
                    <?= AppSessionHandler::csrfField() ?>
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?php echo $household_data['id']; ?>">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Household Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="household_name" required value="<?php echo $household_data['household_name']; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Household ID</label>
                                <input type="text" class="form-control" value="<?php echo $household_data['household_id']; ?>" disabled>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Street Address</label>
                        <input type="text" class="form-control" name="street_address" value="<?php echo $household_data['street_address']; ?>">
                    </div>

                    <div class="row" data-quirino-address data-current-province="<?php echo htmlspecialchars($household_data['province'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" data-current-city="<?php echo htmlspecialchars($household_data['city'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" data-current-barangay="<?php echo htmlspecialchars($household_data['barangay'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label" for="edit-household-province">Province</label>
                                <select class="form-select" id="edit-household-province" name="province"><option value="">Select province</option></select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label" for="edit-household-city">City/Municipality</label>
                                <select class="form-select" id="edit-household-city" name="city" disabled><option value="">Select province first</option></select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label" for="edit-household-barangay">Barangay</label>
                                <select class="form-select" id="edit-household-barangay" name="barangay" disabled><option value="">Select municipality first</option></select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Postal Code</label>
                                <input type="text" class="form-control" name="postal_code" value="<?php echo $household_data['postal_code']; ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Contact Number</label>
                                <input type="text" class="form-control" name="contact_number" value="<?php echo $household_data['contact_number']; ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Total Family Members</label>
                                <input type="number" class="form-control" name="total_members" value="<?php echo $household_data['total_members']; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Monthly Income (PHP)</label>
                                <input type="number" step="0.01" class="form-control" name="monthly_income" value="<?php echo $household_data['monthly_income']; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Eligibility Status</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_eligible" id="is_eligible" <?php echo $household_data['is_eligible'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_eligible">
                                        Eligible for relief assistance
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Eligibility Reason</label>
                        <textarea class="form-control" name="eligibility_reason" rows="2"><?php echo $household_data['eligibility_reason']; ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <div>
                            <a href="households.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Back
                            </a>
                            <button type="button" class="btn btn-danger" onclick="deleteHousehold(<?php echo $household_data['id']; ?>)">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </div>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-check-circle"></i> Update Household
                        </button>
                    </div>
                </form>

                <form id="deleteForm" action="<?= APP_BASE_PATH ?>/controllers/HouseholdController.php" method="POST" style="display: none;">
                    <?= AppSessionHandler::csrfField() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="deleteId">
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../assets/js/quirino-address.js"></script>
    <script>
        function deleteHousehold(id) {
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
