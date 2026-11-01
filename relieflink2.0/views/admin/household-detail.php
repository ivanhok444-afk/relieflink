<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Household Detail - Admin</title>
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
    require_once __DIR__ . '/../../models/Distribution.php';
    require_once __DIR__ . '/../../models/FamilyMember.php';

    $household = new Household();
    $distribution = new Distribution();
    $member = new FamilyMember();

    $id = $_GET['id'] ?? 0;
    $household_data = $household->getHouseholdById($id);
    $distribution_history = $distribution->getDistributionHistory($id);
    $family_members = $member->getMembersByHousehold($id);

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
        <div class="page-title" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <div>
                <i class="bi bi-house-door"></i> <?php echo $household_data['household_name']; ?>
            </div>
            <div>
                <a href="household-edit.php?id=<?php echo $household_data['id']; ?>" class="btn btn-warning btn-sm me-2">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <a href="households.php" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-info-circle"></i> Household Information
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Household ID:</strong></td>
                                <td><code><?php echo $household_data['household_id']; ?></code></td>
                            </tr>
                            <tr>
                                <td><strong>Household QR:</strong></td>
                                <td>
                                    <div class="household-qr" data-household-qr data-qr-value="<?php echo htmlspecialchars($household_data['household_id'], ENT_QUOTES, 'UTF-8'); ?>"></div>
                                    <small class="household-qr-id">Scan code or enter the Household ID.</small>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Name:</strong></td>
                                <td><?php echo $household_data['household_name']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Address:</strong></td>
                                <td><?php echo $household_data['street_address'] . ', ' . $household_data['barangay'] . ', ' . $household_data['city']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Province:</strong></td>
                                <td><?php echo $household_data['province']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Postal Code:</strong></td>
                                <td><?php echo $household_data['postal_code']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Contact:</strong></td>
                                <td><?php echo $household_data['contact_number']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Members:</strong></td>
                                <td><?php echo $household_data['total_members']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Monthly Income:</strong></td>
                                <td>₱<?php echo number_format($household_data['monthly_income'], 2); ?></td>
                            </tr>
                            <tr>
                                <td><strong>Status:</strong></td>
                                <td>
                                    <?php if ($household_data['is_eligible']): ?>
                                        <span class="badge bg-success">Eligible</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Ineligible</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php if (!$household_data['is_eligible'] && $household_data['eligibility_reason']): ?>
                            <tr>
                                <td><strong>Reason:</strong></td>
                                <td><?php echo $household_data['eligibility_reason']; ?></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <i class="bi bi-box-arrow-in-down"></i> Distribution History
                    </div>
                    <div class="card-body">
                        <?php if (empty($distribution_history)): ?>
                            <p class="text-muted text-center">No distributions yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Batch</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($distribution_history as $dist): ?>
                                        <tr>
                                            <td><code><?php echo $dist['batch_code']; ?></code></td>
                                            <td><?php echo date('M d, Y', strtotime($dist['distribution_date'])); ?></td>
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

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-people"></i> Family Members (<?php echo count($family_members); ?>)</span>
                        <a href="family-member-add.php?household_id=<?php echo $household_data['id']; ?>" class="btn btn-sm btn-light">
                            <i class="bi bi-plus-circle"></i> Add Member
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($family_members)): ?>
                            <p class="text-muted text-center">No family members added yet.</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Gender</th>
                                            <th>Age</th>
                                            <th>Relationship</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($family_members as $m): ?>
                                        <tr>
                                            <td><?php echo $m['first_name'] . ' ' . $m['middle_name'] . ' ' . $m['last_name']; ?></td>
                                            <td><?php echo $m['gender'] ?? 'N/A'; ?></td>
                                            <td><?php echo $m['age'] ?? 'N/A'; ?></td>
                                            <td><?php echo $m['relationship'] ?? 'N/A'; ?></td>
                                            <td>
                                                <?php if ($m['is_senior'] || $m['is_pwd']): ?>
                                                    <span class="badge bg-warning"><?php echo $m['is_senior'] ? 'Senior' : ''; ?><?php echo $m['is_senior'] && $m['is_pwd'] ? ', ' : ''; ?><?php echo $m['is_pwd'] ? 'PWD' : ''; ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Regular</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="family-member-edit.php?id=<?php echo $m['id']; ?>" class="btn btn-sm btn-warning">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="javascript:void(0)" onclick="confirmDelete('You won\'t be able to revert this!', '?action=delete&id=<?php echo $m['id']; ?>&household_id=<?php echo $household_data['id']; ?>')" class="btn btn-sm btn-danger">
                                                    <i class="bi bi-trash"></i>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../assets/js/qrcode.min.js"></script>
    <script src="../../assets/js/household-qr.js"></script>
    <script>
        function confirmDelete(message, url) {
            Swal.fire({
                title: 'Are you sure?',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
            return false;
        }
    </script>
</body>
</html>
