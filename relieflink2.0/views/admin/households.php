<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Households Management - Admin</title>
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
        .page-title {
            color: #333;
            margin-bottom: 30px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .btn-action {
            padding: 5px 10px;
            font-size: 12px;
        }
        .card {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border: none;
        }
    </style>
    <link rel="stylesheet" href="../../assets/css/app.css">
</head>
<body>
    <?php
    require_once __DIR__ . '/../../config/SessionHandler.php';
    AppSessionHandler::requireAdmin();

    require_once __DIR__ . '/../../models/Household.php';
    require_once __DIR__ . '/../../models/ReliefRequest.php';
    require_once __DIR__ . '/../../models/User.php';

    $household = new Household();
    $request_model = new ReliefRequest();
    $households_list = $household->getAllHouseholds();

    $requests = $request_model->getAllRequestsWithItems();
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
        <div class="page-title">
            <div>
                <i class="bi bi-people-fill"></i> Households Management
            </div>
            <a href="household-add.php" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Add New Household
            </a>
        </div>

        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" href="#households" data-bs-toggle="tab">Households</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#requests" data-bs-toggle="tab">Relief Requests</a>
            </li>
        </ul>

        <div class="tab-content">
            <div id="households" class="tab-pane fade show active">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-list"></i> Household List
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="householdsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Household ID</th>
                                        <th>Name</th>
                                        <th>Location</th>
                                        <th>Members</th>
                                        <th>Status</th>
                                        <th>Contact</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($households_list as $h): ?>
                                    <tr>
                                        <td><code><?php echo $h['household_id']; ?></code></td>
                                        <td><?php echo $h['household_name']; ?></td>
                                        <td><?php echo $h['barangay'] . ', ' . $h['city']; ?></td>
                                        <td><span class="badge bg-info"><?php echo $h['total_members']; ?></span></td>
                                        <td>
                                            <?php if ($h['is_eligible']): ?>
                                                <span class="badge bg-success">Eligible</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Ineligible</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $h['contact_number']; ?></td>
                                        <td>
                                            <a href="household-detail.php?id=<?php echo $h['id']; ?>" class="btn btn-sm btn-info btn-action">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                            <a href="household-edit.php?id=<?php echo $h['id']; ?>" class="btn btn-sm btn-warning btn-action">
                                                <i class="bi bi-pencil"></i> Edit
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

            <div id="requests" class="tab-pane fade">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <i class="bi bi-chat-dots"></i> Household Relief Requests
                    </div>
                    <div class="card-body">
                        <?php if (empty($requests)): ?>
                            <p class="text-muted text-center">No relief requests submitted yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Household</th>
                                            <th>Household ID</th>
                                            <th>Location</th>
                                            <th>Requested Items</th>
                                            <th>Request Date</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $grouped = [];
                                        foreach ($requests as $r) {
                                            $grouped[$r['id']]['household_name'] = $r['household_name'];
                                            $grouped[$r['id']]['household_id'] = $r['household_id'];
                                            $grouped[$r['id']]['location'] = $r['barangay'] . ', ' . $r['city'];
                                            $grouped[$r['id']]['request_date'] = $r['request_date'];
                                            $grouped[$r['id']]['status'] = $r['status'];
                                            $grouped[$r['id']]['request_reason'] = $r['request_reason'];
                                            $grouped[$r['id']]['items'][] = $r['goods_name'] . ' (' . $r['quantity_requested'] . ' ' . $r['category_name'] . ')';
                                        }
                                        foreach ($grouped as $req_id => $req):
                                        ?>
                                        <tr>
                                            <td><strong><?php echo $req['household_name']; ?></strong></td>
                                            <td><code><?php echo $req['household_id']; ?></code></td>
                                            <td><?php echo $req['location']; ?></td>
                                            <td>
                                                <ul class="mb-0">
                                                    <?php foreach ($req['items'] as $item): ?>
                                                        <li><?php echo $item; ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($req['request_date'])); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $req['status'] == 'Pending' ? 'warning' : ($req['status'] == 'Approved' ? 'success' : ($req['status'] == 'Rejected' ? 'danger' : 'info')); ?>">
                                                    <?php echo $req['status']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="request-detail.php?id=<?php echo $req_id; ?>" class="btn btn-sm btn-info">
                                                    <i class="bi bi-eye"></i> Review
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#householdsTable').DataTable({
                "pageLength": 10,
                "order": [[0, "desc"]]
            });
        });
    </script>
</body>
</html>
