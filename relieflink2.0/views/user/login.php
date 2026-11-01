<?php
require_once __DIR__ . '/../../config/SessionHandler.php';
AppSessionHandler::startSession();

if (isset($_SESSION['user_id'])) {
    $destination = $_SESSION['role'] === 'admin'
        ? '../admin/dashboard.php'
        : 'dashboard.php';
    header('Location: ' . $destination);
    exit;
}

$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error']);
unset($_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#173b35">
    <title>User Sign In - ReliefLink</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet">
    <style>
        :root { --ink: #173b35; --paper: #f5f3ec; --lime: #d9ec86; --coral: #e87557; }
        * { box-sizing: border-box; }
        body { align-items: center; background: var(--paper); color: var(--ink); display: flex; font-family: 'DM Sans', sans-serif; justify-content: center; margin: 0; min-height: 100vh; padding: 32px 18px; }
        .portal { background: #fff; border: 1px solid #e3e3d9; display: grid; grid-template-columns: minmax(0, 1fr) minmax(340px, .9fr); max-width: 940px; min-height: 550px; width: 100%; }
        .intro { background: #173b35; color: white; display: flex; flex-direction: column; justify-content: space-between; overflow: hidden; padding: 38px; position: relative; }
        .intro::after { border: 1px solid rgba(217,236,134,.5); border-radius: 50%; bottom: -100px; content: ''; height: 340px; position: absolute; right: -105px; width: 340px; }
        .brand { color: white; font-size: 14px; font-weight: 700; text-decoration: none; }
        .intro-copy { margin: auto 0; position: relative; z-index: 1; }
        .eyebrow { color: var(--lime); font-size: 11px; font-weight: 700; letter-spacing: 1.6px; text-transform: uppercase; }
        .intro h1 { color: #fff; font: 400 42px/1.08 'DM Serif Display', Georgia, serif; margin: 14px 0; max-width: 340px; }
        .intro-copy p { color: rgba(255,255,255,.75); font-size: 14px; line-height: 1.7; max-width: 330px; }
        .intro-foot { color: rgba(255,255,255,.7); font-size: 12px; position: relative; z-index: 1; }
        .form-panel { align-self: center; padding: 48px; }
        .form-panel h2 { font: 400 30px/1.2 'DM Serif Display', Georgia, serif; margin: 0 0 8px; }
        .subtitle { color: #68756f; font-size: 13px; margin: 0 0 28px; }
        .alert { background: #fff0eb; border-left: 3px solid var(--coral); color: #7d3525; font-size: 13px; line-height: 1.5; margin: 0 0 20px; padding: 12px 14px; }
        .field { margin-bottom: 18px; }
        label { display: block; font-size: 12px; font-weight: 700; margin-bottom: 7px; }
        input { background: #fff; border: 1px solid #cbd2cc; border-radius: 2px; color: var(--ink); font: inherit; height: 46px; padding: 0 12px; width: 100%; }
        input:focus { border-color: var(--ink); outline: 2px solid rgba(23,59,53,.15); }
        .submit { background: var(--ink); border: 0; border-radius: 2px; color: white; cursor: pointer; font: 700 14px 'DM Sans', sans-serif; height: 48px; margin-top: 5px; width: 100%; }
        .submit:hover { background: #24574d; }
        .form-links { display: flex; flex-direction: column; gap: 14px; margin-top: 22px; text-align: center; }
        .form-links a { color: var(--ink); font-size: 12px; font-weight: 600; text-underline-offset: 3px; }
        @media (max-width: 700px) {
            .portal { grid-template-columns: 1fr; max-width: 470px; }
            .intro { min-height: 210px; padding: 25px; }
            .intro-copy { margin: 34px 0 18px; }
            h1 { font-size: 34px; }
            .intro-copy p { margin-bottom: 0; }
            .intro-foot { display: none; }
            .form-panel { padding: 32px 25px; }
        }
    </style>
    <link rel="stylesheet" href="../../assets/css/app.css">
</head>
<body>
    <main class="portal">
        <section class="intro">
            <a class="brand" href="../../landing.php"><span class="brand-wordmark">Relief<span class="brand-accent">Link</span></span></a>
            <div class="intro-copy">
                <div class="eyebrow">Household portal</div>
                <h1>Welcome back.</h1>
                <p>Sign in to manage your household details and relief requests.</p>
            </div>
            <div class="intro-foot">Community support, coordinated with care.</div>
        </section>
        <section class="form-panel" aria-labelledby="login-title">
            <h2 id="login-title">User sign in</h2>
            <p class="subtitle">Enter your account details to continue.</p>
            <?php if ($success !== ''): ?>
                <div class="alert alert-success" role="status"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <?php if ($error !== ''): ?>
                <div class="alert" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <form method="POST" action="../../controllers/AuthController.php">
                <?= AppSessionHandler::csrfField() ?>
                <input type="hidden" name="redirect" value="../views/user/login.php">
                <div class="field">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" autocomplete="username" required autofocus>
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" autocomplete="current-password" required>
                </div>
                <button class="submit" type="submit">Sign in</button>
            </form>
            <nav class="form-links" aria-label="Other account options">
                <a href="../register.php">Create a household account</a>
                <a href="../../landing.php">Back to home</a>
            </nav>
        </section>
    </main>
    <script src="../../assets/js/sweetalert2.all.min.js"></script>
    <script src="../../assets/js/user-actions.js"></script>
</body>
</html>