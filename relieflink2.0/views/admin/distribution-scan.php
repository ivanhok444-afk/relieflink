<?php require_once __DIR__ . '/../../config/SessionHandler.php'; AppSessionHandler::startSession(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan QR - Admin</title>
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
        .qr-scanner {
            max-width: 500px;
            margin: 0 auto;
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }
        .qr-icon {
            font-size: 100px;
            color: var(--primary);
            margin-bottom: 20px;
        }
        #qr-reader { margin: 18px auto; max-width: 360px; }
        #qr-reader video { border-radius: 4px; }
    </style>
    <link rel="stylesheet" href="../../assets/css/app.css">
</head>
<body>
    <?php
    require_once __DIR__ . '/../../config/SessionHandler.php';
    AppSessionHandler::requireAdmin();

    require_once __DIR__ . '/../../models/Distribution.php';
    require_once __DIR__ . '/../../models/Household.php';

    $distribution = new Distribution();
    $household = new Household();
    $batch_id = $_GET['batch_id'] ?? 0;
    $batch = $distribution->getBatchById($batch_id);
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
        <div class="page-title" style="margin-bottom: 30px; text-align: center;">
            <i class="bi bi-qr-code-scan"></i> Scan Household QR Code
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="qr-scanner">
            <div class="qr-icon">
                <i class="bi bi-qr-code-scan"></i>
            </div>

            <h4>Batch: <?php echo $batch['batch_code']; ?></h4>
            <p class="text-muted"><?php echo date('M d, Y', strtotime($batch['batch_date'])); ?></p>

            <form action="<?= APP_BASE_PATH ?>/controllers/DistributionController.php" method="POST" class="mt-4" data-household-scan-form>
                <?= AppSessionHandler::csrfField() ?>
                <input type="hidden" name="action" value="scan_qr">
                <input type="hidden" name="batch_id" value="<?php echo $batch_id; ?>">

                <div class="mb-3">
                    <label class="form-label" for="household-code">Scan QR Code or Enter Household ID</label>
                    <input type="text" class="form-control form-control-lg text-center"
                           id="household-code" name="qr_code" required autofocus autocomplete="off"
                           placeholder="Scan QR or type Household ID"
                           style="font-size: 18px; letter-spacing: 2px;">
                </div>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-primary w-100" id="start-qr-camera">
                        <i class="bi bi-camera-video"></i> Scan with camera
                    </button>
                    <button type="button" class="btn btn-outline-primary w-100" id="stop-qr-camera" hidden>
                        <i class="bi bi-stop-circle"></i> Stop camera
                    </button>
                </div>
                <div id="qr-reader" hidden></div>
                <p class="small text-muted mt-2" id="qr-scan-status" aria-live="polite">Use the camera or enter the Household ID manually.</p>

                <button type="submit" class="btn btn-primary btn-lg w-100">
                    <i class="bi bi-search"></i> Identify Household
                </button>
            </form>

            <div class="mt-4">
                <a href="distribution-detail.php?id=<?php echo $batch_id; ?>" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Batch
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/html5-qrcode.min.js"></script>
    <script src="../../assets/js/household-qr-scan.js"></script>
    <script>
        document.querySelector('input[name="qr_code"]').focus();
    </script>
</body>
</html>
