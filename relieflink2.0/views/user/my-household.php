<?php require_once __DIR__ . '/../../config/SessionHandler.php'; AppSessionHandler::startSession(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Household - ReliefLink</title>
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
    AppSessionHandler::requireLogin();

    require_once __DIR__ . '/../../models/Household.php';
    require_once __DIR__ . '/../../models/FamilyMember.php';

    $household = new Household();
    $member = new FamilyMember();

    $user_id = AppSessionHandler::getUserId();
    $user_data = null;
    $household_data = $household->getHouseholdByUserId($user_id);
    $family_members = $household_data ? $member->getMembersByHousehold($household_data['id']) : [];
    ?>

    <!-- Navbar -->
    <nav class="navbar navbar-dark navbar-expand-lg fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= APP_BASE_PATH ?>/views/user/dashboard.php">
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

    <!-- Sidebar -->
    <div class="sidebar">
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-link">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="my-household.php" class="nav-link active">
                <i class="bi bi-person-fill"></i> My Household
            </a>
            <a href="relief-request.php" class="nav-link">
                <i class="bi bi-chat-dots"></i> Relief Request
            </a>
            <a href="business-chat.php" class="nav-link">
                <i class="bi bi-chat-square-text"></i> Chat with Admin
            </a>
            <a href="distribution-history.php" class="nav-link">
                <i class="bi bi-clock-history"></i> Distribution History
            </a>
            <a href="profile.php" class="nav-link">
                <i class="bi bi-person-circle"></i> My Profile
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-title" style="margin-bottom: 30px;">
            <i class="bi bi-person-fill"></i> My Household Information
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-info-circle"></i> Household Details
                    </div>
                    <div class="card-body">
                        <?php if (!$household_data): ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i> You don't have a household registered yet. Please contact the administrator.
                            </div>
                        <?php else: ?>
                        <form action="<?= APP_BASE_PATH ?>/controllers/UserProfileController.php" method="POST" data-swal-confirm="Save household changes?" data-swal-text="Your household details will be updated. Address changes must be requested from the administrator." data-swal-confirm-button="Save changes">
                            <?= AppSessionHandler::csrfField() ?>
                            <input type="hidden" name="action" value="update_household">
                            <input type="hidden" name="redirect" value="my-household.php">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Household ID</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($household_data['household_id'], ENT_QUOTES, 'UTF-8'); ?>" disabled>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Household Name</label>
                                        <input type="text" class="form-control" name="household_name" value="<?php echo htmlspecialchars($household_data['household_name'], ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="registered-address">Registered Address</label>
                                <?php
                                $addressParts = array_filter([
                                    $household_data['street_address'] ?? '',
                                    $household_data['barangay'] ?? '',
                                    $household_data['city'] ?? '',
                                    $household_data['province'] ?? '',
                                    $household_data['postal_code'] ?? ''
                                ], function ($part) {
                                    return trim((string) $part) !== '';
                                });
                                $registeredAddress = implode(', ', $addressParts);
                                ?>
                                <input type="text" class="form-control" id="registered-address" value="<?php echo htmlspecialchars($registeredAddress, ENT_QUOTES, 'UTF-8'); ?>" readonly>
                                <div class="form-text">Hindi maaaring baguhin ang address dito. <a href="business-chat.php">Makipag-ugnayan sa admin</a> kung kailangan itong itama o palitan.</div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Contact Number</label>
                                        <input type="tel" class="form-control" name="contact_number" value="<?php echo htmlspecialchars($household_data['contact_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Total Family Members</label>
                                        <input type="number" class="form-control" name="total_members" value="<?php echo (int) $household_data['total_members']; ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Monthly Income (PHP)</label>
                                <input type="number" step="0.01" class="form-control" name="monthly_income" value="<?php echo htmlspecialchars((string) $household_data['monthly_income'], ENT_QUOTES, 'UTF-8'); ?>">
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Update Information
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <?php if ($household_data): ?>
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">
                        <i class="bi bi-qr-code"></i> QR Code
                    </div>
                    <div class="card-body text-center">
                        <div class="household-qr" data-household-qr data-qr-value="<?php echo htmlspecialchars($household_data['household_id'], ENT_QUOTES, 'UTF-8'); ?>"></div>
                        <p class="household-qr-id mt-2 mb-0"><?php echo htmlspecialchars($household_data['household_id'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="text-muted mt-2 mb-0">Show this code at relief distribution.</p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-info text-white">
                        <i class="bi bi-check-circle"></i> Status
                    </div>
                    <div class="card-body">
                        <p><strong>Eligibility:</strong></p>
                        <p>
                            <?php if ($household_data['is_eligible']): ?>
                                <span class="badge bg-success">Eligible</span>
                            <?php else: ?>
                                <span class="badge bg-warning">Ineligible</span>
                            <?php endif; ?>
                        </p>
                        <hr>
                        <p><strong>Last Verified:</strong></p>
                        <p class="text-muted"><?php echo $household_data['updated_at'] ? date('M d, Y', strtotime($household_data['updated_at'])) : 'Not yet verified'; ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Family Members Section -->
        <?php if ($household_data): ?>
        <div class="card mt-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <span><i class="bi bi-people-fill"></i> Family Members (<?php echo count($family_members); ?>)</span>
                <a href="../admin/family-member-add.php?household_id=<?php echo $household_data['id']; ?>" class="btn btn-sm btn-light">
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
                                    <th>Age</th>
                                    <th>Relationship</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($family_members as $m): ?>
                                <tr>
                                    <td><?php echo $m['first_name'] . ' ' . $m['last_name']; ?></td>
                                    <td><?php echo $m['age'] ?? 'N/A'; ?></td>
                                    <td><?php echo $m['relationship'] ?? 'N/A'; ?></td>
                                    <td>
                                        <?php if ($m['is_senior'] || $m['is_pwd']): ?>
                                            <span class="badge bg-warning"><?php echo $m['is_senior'] ? 'Senior' : ''; ?><?php echo $m['is_senior'] && $m['is_pwd'] ? ', ' : ''; ?><?php echo $m['is_pwd'] ? 'PWD' : ''; ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Regular</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/sweetalert2.all.min.js"></script>
    <script src="../../assets/js/user-actions.js"></script>
    <script src="../../assets/js/qrcode.min.js"></script>
    <script src="../../assets/js/household-qr.js"></script>
</body>
</html>
