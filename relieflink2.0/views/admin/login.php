<?php
require_once __DIR__ . '/../../config/SessionHandler.php';
AppSessionHandler::startSession();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - ReliefLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }
        .login-header {
            background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
            border-bottom: 4px solid #e94560;
        }
        .login-header h1 {
            font-size: 28px;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .login-header p {
            margin: 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .admin-badge {
            display: inline-block;
            background: #e94560;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 10px;
            letter-spacing: 1px;
        }
        .login-body {
            padding: 40px;
        }
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px 15px;
            transition: border-color 0.3s;
        }
        .form-control:focus {
            border-color: #0f3460;
            box-shadow: 0 0 0 0.2rem rgba(15, 52, 96, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #1a1a2e 0%, #0f3460 100%);
            border: none;
            padding: 12px 20px;
            font-weight: 600;
            border-radius: 8px;
            width: 100%;
            transition: transform 0.2s;
            color: white;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            color: white;
        }
        .alert {
            border-radius: 8px;
            border: none;
        }
        .text-center a {
            color: #0f3460;
            text-decoration: none;
            font-weight: 500;
        }
        .text-center a:hover {
            text-decoration: underline;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }
        .back-link a {
            color: #666;
            text-decoration: none;
            font-size: 14px;
        }
        .back-link a:hover {
            color: #0f3460;
        }
    </style>
    <link rel="stylesheet" href="../../assets/css/app.css">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div style="font-size: 48px; margin-bottom: 10px;">
                <i class="bi bi-shield-lock"></i>
            </div>
            <h1>Admin Portal</h1>
            <p class="brand-wordmark">Relief<span class="brand-accent">Link</span></p>
            <span class="admin-badge">RESTRICTED ACCESS</span>
        </div>

        <div class="login-body">
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

            <form method="POST" action="../../controllers/AuthController.php">
                <?= AppSessionHandler::csrfField() ?>
                <input type="hidden" name="redirect" value="../views/admin/login.php">
                <div class="mb-3">
                    <label class="form-label" for="username">
                        <i class="bi bi-person-circle"></i> Admin Username
                    </label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Enter admin username" required autofocus>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="password">
                        <i class="bi bi-lock-circle"></i> Password
                    </label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-login">
                    <i class="bi bi-box-arrow-in-right"></i> Admin Login
                </button>
            </form>

            <div class="back-link">
                <a href="../../landing.php"><i class="bi bi-arrow-left"></i> Back to Home</a>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
