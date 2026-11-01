<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ReliefLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
        }
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .sidebar {
            background: white;
            min-height: 100vh;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
            position: fixed;
            width: 250px;
            left: 0;
            top: 56px;
        }
        .sidebar-nav .nav-link {
            color: #333;
            padding: 12px 20px;
            border-left: 4px solid transparent;
            transition: all 0.3s;
        }
        .sidebar-nav .nav-link:hover {
            background-color: #f0f4ff;
            border-left-color: var(--primary);
            color: var(--primary);
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
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border-top: 4px solid;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card.households {
            border-top-color: #667eea;
        }
        .stat-card.goods {
            border-top-color: #f093fb;
        }
        .stat-card.distributed {
            border-top-color: #4facfe;
        }
        .stat-card.pending {
            border-top-color: #fa709a;
        }
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #333;
        }
        .stat-label {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
        .stat-icon {
            font-size: 40px;
            opacity: 0.2;
            float: right;
        }
        .page-title {
            color: #333;
            margin-bottom: 30px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        @media (max-width: 768px) {
            .sidebar {
                display: none;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
    <link rel="stylesheet" href="../../assets/css/app.css">
</head>
<body>
    <?php
    require_once __DIR__ . '/../../config/SessionHandler.php';
    AppSessionHandler::requireAdmin();

    require_once __DIR__ . '/../../models/Household.php';
    require_once __DIR__ . '/../../models/ReliefGoods.php';
    require_once __DIR__ . '/../../models/Distribution.php';
    require_once __DIR__ . '/../../models/ReliefRequest.php';

    $household = new Household();
    $goods = new ReliefGoods();
    $distribution = new Distribution();
    $request = new ReliefRequest();

    $total_households = $household->getHouseholdCount();
    $eligible_households = $household->getEligibleHouseholds();
    $total_stock = $goods->getTotalStock();
    $total_distributed = $distribution->getTotalDistributed();
    $pending_requests = $request->getPendingCount();

    $distribution_chart_labels = [];
    $distribution_chart_counts = [];
    $monthly_distribution_counts = $distribution->getMonthlyDistributionCounts(6);
    $chart_month = new DateTimeImmutable('first day of this month');
    for ($month_offset = 5; $month_offset >= 0; $month_offset--) {
        $month = $chart_month->modify('-' . $month_offset . ' months');
        $month_key = $month->format('Y-m');
        $distribution_chart_labels[] = $month->format('M Y');
        $distribution_chart_counts[] = $monthly_distribution_counts[$month_key] ?? 0;
    }
    ?>

    <!-- Navbar -->
    <nav class="navbar navbar-dark navbar-expand-lg fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= APP_BASE_PATH ?>/views/admin/dashboard.php">
                <i class="bi bi-box-seam"></i> <span class="brand-wordmark">Relief<span class="brand-accent">Link</span></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto admin-navbar-actions">
                    <li class="nav-item admin-welcome">
                        <span class="navbar-text">Welcome, <strong><?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?></strong></span>
                    </li>
                    <li class="nav-item admin-logout">
                        <a class="nav-link" href="<?= APP_BASE_PATH ?>/controllers/LogoutController.php" onclick="return confirmAction('Are you sure you want to logout?', function(){ window.location.href = '<?= APP_BASE_PATH ?>/controllers/LogoutController.php'; })">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <?php require __DIR__ . '/admin_sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content" data-admin-user-id="<?= (int) AppSessionHandler::getUserId() ?>">
        <div class="page-title">
            <i class="bi bi-speedometer2"></i> Dashboard
        </div>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-6 col-lg-3">
                <a class="dashboard-stat-link" href="households.php" aria-label="View <?= (int) $total_households ?> households">
                    <div class="stat-card households">
                        <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
                        <div class="stat-number"><?php echo $total_households; ?></div>
                        <div class="stat-label">Total Households</div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-3">
                <a class="dashboard-stat-link" href="relief-goods.php" aria-label="View <?= (int) $total_stock ?> relief goods">
                    <div class="stat-card goods">
                        <div class="stat-icon"><i class="bi bi-box-seam"></i></div>
                        <div class="stat-number"><?php echo $total_stock; ?></div>
                        <div class="stat-label">Total Relief Goods</div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-3">
                <a class="dashboard-stat-link" href="distributions.php" aria-label="View <?= (int) $total_distributed ?> distribution batches">
                    <div class="stat-card distributed">
                        <div class="stat-icon"><i class="bi bi-arrow-left-right"></i></div>
                        <div class="stat-number"><?php echo $total_distributed; ?></div>
                        <div class="stat-label">Distributed Batches</div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-3">
                <a class="dashboard-stat-link" href="requests.php#pending" aria-label="Review <?= (int) $pending_requests ?> pending relief requests">
                    <div class="stat-card pending">
                        <div class="stat-icon"><i class="bi bi-chat-dots"></i></div>
                        <div class="stat-number" data-pending-requests-count><?= (int) $pending_requests ?></div>
                        <div class="stat-label">Pending Requests · Review</div>
                    </div>
                </a>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-bar-chart-line"></i> Relief Distribution Chart
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Households receiving relief distributions by month over the last six months.</p>
                        <div style="height: 320px; position: relative;">
                            <canvas id="reliefDistributionChart" role="img" aria-label="Monthly number of household relief distributions for the last six months">
                                Monthly relief distribution counts for the last six months.
                            </canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Statistics -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-check-circle"></i> Eligible Households
                    </div>
                    <div class="card-body">
                        <h3><?php echo $eligible_households; ?> out of <?php echo $total_households; ?></h3>
                        <p class="text-muted">Households eligible for relief assistance</p>
                        <a href="households.php" class="btn btn-sm btn-primary">View Details</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        <i class="bi bi-exclamation-circle"></i> Quick Actions
                    </div>
                    <div class="card-body d-flex flex-wrap align-items-center gap-2">
                        <a href="households.php?action=add" class="btn btn-sm btn-info" onclick="return confirmAction('Go to Add Household form?', function(){ window.location.href = 'households.php?action=add'; })">
                            <i class="bi bi-plus-circle"></i> Add Household
                        </a>
                        <a href="relief-goods.php?action=add" class="btn btn-sm btn-info" onclick="return confirmAction('Go to Add Relief Goods form?', function(){ window.location.href = 'relief-goods.php?action=add'; })">
                            <i class="bi bi-plus-circle"></i> Add Relief Goods
                        </a>
                        <a href="distributions.php?action=create" class="btn btn-sm btn-success" onclick="return confirmAction('Go to Create Distribution Batch form?', function(){ window.location.href = 'distributions.php?action=create'; })">
                            <i class="bi bi-plus-circle"></i> Create Distribution
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <i class="bi bi-clock-history"></i> Recent Activities
                    </div>
                    <div class="card-body">
                        <p class="text-muted">System is ready. Start by managing households, relief goods, and distributions.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../assets/js/admin-request-alerts.js" defer></script>
    <script>
        const distributionChartCanvas = document.getElementById('reliefDistributionChart');
        if (window.Chart && distributionChartCanvas) {
            new Chart(distributionChartCanvas, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($distribution_chart_labels, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
                    datasets: [{
                        label: 'Household distributions',
                        data: <?= json_encode($distribution_chart_counts) ?>,
                        backgroundColor: 'rgba(79, 172, 254, 0.7)',
                        borderColor: '#287bb5',
                        borderWidth: 1,
                        borderRadius: 6
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 },
                            title: { display: true, text: 'Households served' }
                        },
                        x: {
                            title: { display: true, text: 'Month' }
                        }
                    },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (context) => `${context.parsed.y} household distribution${context.parsed.y === 1 ? '' : 's'}`
                            }
                        }
                    }
                }
            });
        }

        function confirmAction(message, callback) {
            Swal.fire({
                title: 'Are you sure?',
                text: message,
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
