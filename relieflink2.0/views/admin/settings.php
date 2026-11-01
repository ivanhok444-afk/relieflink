<?php
require_once __DIR__ . '/../../config/SessionHandler.php';
AppSessionHandler::requireAdmin();
$settingsError = $_SESSION['error'] ?? '';
$settingsSuccess = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings - Admin</title>
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
            <i class="bi bi-gear"></i> System Settings
        </div>

        <?php if ($settingsError !== ''): ?>
            <div class="alert alert-danger" role="alert"><?= htmlspecialchars($settingsError, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if ($settingsSuccess !== ''): ?>
            <div class="alert alert-success" role="status"><?= htmlspecialchars($settingsSuccess, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" href="#general" data-bs-toggle="tab">General</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#system" data-bs-toggle="tab">System</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#notifications" data-bs-toggle="tab">Notifications</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#security" data-bs-toggle="tab">Security</a>
            </li>
        </ul>

        <div class="tab-content">
            <!-- General Settings -->
            <div id="general" class="tab-pane fade show active">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-info-circle"></i> General Settings
                    </div>
                    <div class="card-body">
                        <form>
                            <div class="mb-3">
                                <label class="form-label">System Name</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">System Email</label>
                                <input type="email" class="form-control" value="admin@reliefgoods.com">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Contact Number</label>
                                <input type="tel" class="form-control" placeholder="Enter contact number">
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Save Changes
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- System Settings -->
            <div id="system" class="tab-pane fade">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-sliders"></i> System Configuration
                    </div>
                    <div class="card-body">
                        <form>
                            <div class="mb-3">
                                <label class="form-label">Max Goods per Household</label>
                                <input type="number" class="form-control" value="5">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Require Admin Approval for Requests</label>
                                <select class="form-select">
                                    <option selected>Yes</option>
                                    <option>No</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Low Stock Threshold</label>
                                <input type="number" class="form-control" value="10">
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Save Configuration
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Notifications -->
            <div id="notifications" class="tab-pane fade">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-bell"></i> Notification Settings
                    </div>
                    <div class="card-body">
                        <form>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="notifyPending" checked>
                                    <label class="form-check-label" for="notifyPending">
                                        Notify on pending requests
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="notifyLowStock" checked>
                                    <label class="form-check-label" for="notifyLowStock">
                                        Notify on low stock items
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="notifyExpiration" checked>
                                    <label class="form-check-label" for="notifyExpiration">
                                        Notify on expiring items
                                    </label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Save Preferences
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div id="security" class="tab-pane fade">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-shield-lock"></i> Change Admin Password
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Confirm your current password before choosing a new one. The new password must be at least <?= (int) PASSWORD_MIN_LENGTH ?> characters.</p>
                        <form action="<?= APP_BASE_PATH ?>/controllers/AdminPasswordController.php" method="POST">
                            <?= AppSessionHandler::csrfField() ?>
                            <div class="mb-3">
                                <label class="form-label" for="current_password">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" autocomplete="current-password" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="new_password">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" autocomplete="new-password" minlength="<?= (int) PASSWORD_MIN_LENGTH ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="confirm_password">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" autocomplete="new-password" minlength="<?= (int) PASSWORD_MIN_LENGTH ?>" required>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-key"></i> Change Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        if (window.location.hash === '#security') {
            const securityTab = document.querySelector('a[href="#security"]');
            if (securityTab) {
                bootstrap.Tab.getOrCreateInstance(securityTab).show();
            }
        }
    </script>
</body>
</html>
