<?php require_once __DIR__ . '/../../config/SessionHandler.php'; AppSessionHandler::startSession(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - ReliefLink</title>
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

    require_once __DIR__ . '/../../models/User.php';

    $user = new User();
    $user_id = AppSessionHandler::getUserId();
    $user_data = $user->getUserById($user_id);
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
            <a href="my-household.php" class="nav-link">
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
            <a href="profile.php" class="nav-link active">
                <i class="bi bi-person-circle"></i> My Profile
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="page-title" style="margin-bottom: 30px;">
            <i class="bi bi-person-circle"></i> My Profile
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
                        <i class="bi bi-person-check"></i> Account Information
                    </div>
                    <div class="card-body">
                        <form action="<?= APP_BASE_PATH ?>/controllers/UserProfileController.php" method="POST" data-swal-confirm="Save profile changes?" data-swal-text="Your name and email address will be updated." data-swal-confirm-button="Save changes">
                            <?= AppSessionHandler::csrfField() ?>
                            <input type="hidden" name="action" value="update_profile">
                            <input type="hidden" name="redirect" value="profile.php">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">First Name</label>
                                        <input type="text" class="form-control" name="first_name" value="<?php echo $user_data['first_name']; ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Last Name</label>
                                        <input type="text" class="form-control" name="last_name" value="<?php echo $user_data['last_name']; ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" value="<?php echo $user_data['username']; ?>" disabled>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" name="email" value="<?php echo $user_data['email']; ?>">
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Update Profile
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header bg-warning text-dark">
                        <i class="bi bi-shield-lock"></i> Change Password
                    </div>
                    <div class="card-body">
                        <form action="<?= APP_BASE_PATH ?>/controllers/UserProfileController.php" method="POST" data-swal-confirm="Change your password?" data-swal-text="You will need to use the new password the next time you sign in." data-swal-confirm-button="Update password">
                            <?= AppSessionHandler::csrfField() ?>
                            <input type="hidden" name="action" value="change_password">
                            <input type="hidden" name="redirect" value="profile.php">

                            <div class="mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" class="form-control" name="current_password" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" class="form-control" name="new_password" required minlength="12">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" name="confirm_password" required>
                            </div>

                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-check-circle"></i> Update Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <i class="bi bi-info-circle"></i> Account Details
                    </div>
                    <div class="card-body">
                        <p><strong>User Type:</strong></p>
                        <p><span class="badge bg-info"><?php echo ucfirst($user_data['role']); ?></span></p>
                        <hr>
                        <p><strong>Account Status:</strong></p>
                        <p><span class="badge bg-<?php echo $user_data['is_active'] ? 'success' : 'danger'; ?>"><?php echo $user_data['is_active'] ? 'Active' : 'Inactive'; ?></span></p>
                        <hr>
                        <p><strong>Member Since:</strong></p>
                        <p class="text-muted"><?php echo date('M d, Y', strtotime($user_data['created_at'])); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/sweetalert2.all.min.js"></script>
    <script src="../../assets/js/user-actions.js"></script>
</body>
</html>
