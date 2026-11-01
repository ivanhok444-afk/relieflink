<?php
require_once __DIR__ . '/../../config/SessionHandler.php';
AppSessionHandler::requireLogin();
if (AppSessionHandler::isAdmin()) {
    header('Location: ' . APP_BASE_PATH . '/views/admin/business-chat.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with Admin - ReliefLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/app.css">
</head>
<body>
    <nav class="navbar navbar-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php"><i class="bi bi-box-seam"></i> <span class="brand-wordmark">Relief<span class="brand-accent">Link</span></span></a>
            <a class="nav-link" href="<?= APP_BASE_PATH ?>/controllers/LogoutController.php">Logout</a>
        </div>
    </nav>
    <main class="chat-page" data-business-chat data-chat-role="user"
          data-user-id="<?= (int) AppSessionHandler::getUserId() ?>"
          data-csrf-token="<?= htmlspecialchars(AppSessionHandler::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
        <header class="chat-page-heading">
            <div>
                <p class="chat-eyebrow">Household support</p>
                <h1>Chat with Admin</h1>
                <p class="text-muted">Send a private message to the ReliefLink administration team.</p>
            </div>
            <a class="btn btn-outline-primary" href="dashboard.php"><i class="bi bi-arrow-left"></i> Dashboard</a>
        </header>
        <section class="chat-panel">
            <div class="chat-panel-heading"><i class="bi bi-chat-dots"></i> Your conversation</div>
            <div class="chat-messages" data-chat-messages aria-live="polite"></div>
            <p class="chat-status" data-chat-status role="status">Loading conversation…</p>
            <form class="chat-compose" data-chat-form>
                <label class="visually-hidden" for="chat-message">Your message</label>
                <textarea id="chat-message" data-chat-input maxlength="2000" rows="3" placeholder="Write your message…" required></textarea>
                <button class="btn btn-primary" type="submit"><i class="bi bi-send"></i> Send</button>
            </form>
        </section>
    </main>
    <script src="../../assets/js/business-chat.js" defer></script>
</body>
</html>
