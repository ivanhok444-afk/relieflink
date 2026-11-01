<?php
require_once __DIR__ . '/../../config/SessionHandler.php';
AppSessionHandler::requireAdmin();
$conversationId = filter_var($_GET['conversation_id'] ?? 0, FILTER_VALIDATE_INT);
if ($conversationId === false || $conversationId < 0) {
    $conversationId = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Chat - ReliefLink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/app.css">
    <style>
        /* Keep the sidebar out of the document flow (matches every other admin page),
           otherwise it pushes the chat content far down the page. */
        .sidebar {
            left: 0;
            min-height: 100vh;
            position: fixed;
        }
        @media (min-width: 769px) {
            .chat-admin-page { margin-left: 248px; }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark navbar-expand-lg fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= APP_BASE_PATH ?>/views/admin/dashboard.php"><i class="bi bi-box-seam"></i> <span class="brand-wordmark">Relief<span class="brand-accent">Link</span></span></a>
            <div class="ms-auto">
                <span class="navbar-text me-3">Welcome, <strong><?= htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8') ?></strong></span>
                <a class="btn btn-sm btn-light" href="<?= APP_BASE_PATH ?>/controllers/LogoutController.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
            </div>
        </div>
    </nav>
    <?php require __DIR__ . '/admin_sidebar.php'; ?>
    <main class="chat-page chat-admin-page" data-business-chat data-chat-role="admin"
          data-user-id="<?= (int) AppSessionHandler::getUserId() ?>"
          data-conversation-id="<?= (int) $conversationId ?>"
          data-csrf-token="<?= htmlspecialchars(AppSessionHandler::csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
        <header class="chat-page-heading">
            <div>
                <p class="chat-eyebrow">ReliefLink support</p>
                <h1>Business Chat</h1>
                <p class="text-muted">Private conversations with household accounts.</p>
            </div>
            <a class="btn btn-outline-primary" href="dashboard.php"><i class="bi bi-arrow-left"></i> Dashboard</a>
        </header>
        <section class="chat-workspace">
            <aside class="chat-inbox">
                <div class="chat-panel-heading"><i class="bi bi-inbox"></i> Conversations</div>
                <div data-chat-conversations aria-label="User conversations"></div>
            </aside>
            <section class="chat-panel chat-thread">
                <div class="chat-panel-heading"><i class="bi bi-chat-dots"></i> Conversation</div>
                <div class="chat-messages" data-chat-messages aria-live="polite"></div>
                <p class="chat-status" data-chat-status role="status">Choose a conversation to begin.</p>
                <form class="chat-compose" data-chat-form>
                    <label class="visually-hidden" for="chat-message">Your reply</label>
                    <textarea id="chat-message" data-chat-input maxlength="2000" rows="3" placeholder="Write a reply…" required></textarea>
                    <button class="btn btn-primary" type="submit"><i class="bi bi-send"></i> Send</button>
                </form>
            </section>
        </section>
    </main>
    <script src="../../assets/js/business-chat.js" defer></script>
</body>
</html>
