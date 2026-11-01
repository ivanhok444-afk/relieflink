<?php
require_once __DIR__ . '/../config/SessionHandler.php';
AppSessionHandler::startSession();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - ReliefLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
        }
        .register-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(23, 59, 53, 0.12);
            overflow: hidden;
            max-width: 640px;
            width: 100%;
        }
        .register-header {
            padding: 34px 24px;
            text-align: center;
        }
        .register-header .register-icon {
            font-size: 44px;
            line-height: 1;
            margin-bottom: 12px;
        }
        .register-header h1 {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .register-header p {
            margin: 0;
            font-size: 13px;
            opacity: 0.85;
        }
        .register-body {
            padding: 32px 36px 36px;
        }
        .register-body .form-label {
            font-weight: 600;
            margin-bottom: 6px;
        }
        .register-body .form-control,
        .register-body .form-select {
            border: 1px solid #cbd2cc;
            border-radius: 6px;
            min-height: 44px;
            padding: 10px 12px;
        }
        .register-body .form-control:focus,
        .register-body .form-select:focus {
            border-color: #78934b;
            box-shadow: 0 0 0 0.18rem rgba(121, 147, 75, 0.15);
        }
        .register-body .form-check-label {
            font-size: 13px;
        }
        .btn-register {
            border: none;
            border-radius: 6px;
            font-weight: 700;
            padding: 12px 20px;
            transition: background 0.2s;
            width: 100%;
        }
        .btn-register:hover {
            color: #fff;
        }
        .login-link {
            font-size: 13px;
            margin-top: 20px;
            text-align: center;
        }
        .login-link a {
            color: var(--app-ink, #173b35);
            font-weight: 600;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        @media (max-width: 575.98px) {
            .register-header {
                padding: 26px 18px;
            }
            .register-body {
                padding: 24px 20px 28px;
            }
        }
    </style>
    <link rel="stylesheet" href="../assets/css/app.css">
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <div class="register-icon">
                <i class="bi bi-person-plus"></i>
            </div>
            <h1>Create Account</h1>
            <p>Join ReliefLink</p>
        </div>

        <div class="register-body">
            <?php
            if (isset($_SESSION['error'])) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
                echo '<i class="bi bi-exclamation-circle"></i> ' . $_SESSION['error'];
                echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                echo '</div>';
                unset($_SESSION['error']);
            }

            if (isset($_SESSION['success'])) {
                echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
                echo '<i class="bi bi-check-circle"></i> ' . $_SESSION['success'];
                echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                echo '</div>';
                unset($_SESSION['success']);
            }
            ?>

            <form method="POST" action="../controllers/RegisterController.php" data-swal-confirm="Create your account?" data-swal-text="Please confirm that your registration details are correct." data-swal-confirm-button="Create account">
                <?= AppSessionHandler::csrfField() ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="first_name">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" placeholder="First Name" autocomplete="given-name" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="last_name">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Last Name" autocomplete="family-name" required>
                    </div>
                </div>

                <div class="mb-3 mt-3">
                    <label class="form-label" for="email">
                        <i class="bi bi-envelope"></i> Email Address
                    </label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="your@email.com" autocomplete="email" required>
                </div>

                <div class="row g-3" data-quirino-address>
                    <div class="col-md-4">
                        <label class="form-label" for="register-province">Province</label>
                        <select class="form-select" id="register-province" name="province" autocomplete="address-level1">
                            <option value="">Select province</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="register-city">Municipality</label>
                        <select class="form-select" id="register-city" name="city" autocomplete="address-level2" disabled>
                            <option value="">Select province first</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="register-barangay">Barangay</label>
                        <select class="form-select" id="register-barangay" name="barangay" autocomplete="address-level3" disabled>
                            <option value="">Select municipality first</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3 mt-3">
                    <label class="form-label" for="username">
                        <i class="bi bi-person-circle"></i> Username
                    </label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Choose a username" autocomplete="username" required>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="password">
                        <i class="bi bi-lock-circle"></i> Password
                    </label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Create a strong password" autocomplete="new-password" required minlength="12">
                    <small class="text-muted">Minimum 12 characters</small>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="confirm_password">
                        <i class="bi bi-lock-circle"></i> Confirm Password
                    </label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm password" autocomplete="new-password" required>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="agree" name="agree" required>
                        <label class="form-check-label" for="agree">
                            I agree to the Terms and Conditions
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-register">
                    <i class="bi bi-person-plus"></i> Create Account
                </button>
            </form>

            <div class="login-link">
                <p>Already have an account? <a href="user/login.php">Login here</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/quirino-address.js"></script>
    <script src="../assets/js/sweetalert2.all.min.js"></script>
    <script src="../assets/js/user-actions.js"></script>
</body>
</html>
