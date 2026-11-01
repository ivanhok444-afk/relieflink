<?php
/**
 * Shared admin sidebar.
 *
 * Included by every page under views/admin so that the navigation menu
 * (including Business Chat) stays consistent across the whole admin panel.
 * The active menu item is derived from the current script name, so sub-pages
 * such as household-detail.php still highlight their parent section.
 */
require_once __DIR__ . '/../../config/SessionHandler.php';
AppSessionHandler::requireAdmin();

require_once __DIR__ . '/../../models/ReliefRequest.php';
$sidebar_pending_requests = (new ReliefRequest())->getPendingCount();

$sidebar_active_map = [
    'dashboard.php' => 'dashboard',
    'households.php' => 'households',
    'household-add.php' => 'households',
    'household-edit.php' => 'households',
    'household-detail.php' => 'households',
    'family-member-add.php' => 'households',
    'family-member-edit.php' => 'households',
    'relief-goods.php' => 'relief-goods',
    'goods-add.php' => 'relief-goods',
    'goods-edit.php' => 'relief-goods',
    'goods-detail.php' => 'relief-goods',
    'distributions.php' => 'distributions',
    'distribution-create.php' => 'distributions',
    'distribution-detail.php' => 'distributions',
    'distribution-distribute.php' => 'distributions',
    'distribution-eligibility.php' => 'distributions',
    'distribution-scan.php' => 'distributions',
    'distribution-receipt.php' => 'distributions',
    'requests.php' => 'requests',
    'request-detail.php' => 'requests',
    'business-chat.php' => 'business-chat',
    'reports.php' => 'reports',
    'report-distribution.php' => 'reports',
    'report-inventory.php' => 'reports',
    'report-household.php' => 'reports',
    'report-beneficiaries.php' => 'reports',
    'report-activity.php' => 'reports',
    'report-requests.php' => 'reports',
    'users.php' => 'users',
    'user-create.php' => 'users',
    'user-edit.php' => 'users',
    'users-by-location.php' => 'users',
    'settings.php' => 'settings',
];

$sidebar_current = basename($_SERVER['SCRIPT_NAME'] ?? '');
$sidebar_active = $sidebar_active_map[$sidebar_current] ?? '';

if (!function_exists('admin_sidebar_item_class')) {
    function admin_sidebar_item_class(string $key, string $active): string
    {
        return $key === $active ? 'nav-link active' : 'nav-link';
    }
}
?>
    <!-- Sidebar -->
    <div class="sidebar">
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="<?= admin_sidebar_item_class('dashboard', $sidebar_active) ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="households.php" class="<?= admin_sidebar_item_class('households', $sidebar_active) ?>">
                <i class="bi bi-people-fill"></i> Households
            </a>
            <a href="relief-goods.php" class="<?= admin_sidebar_item_class('relief-goods', $sidebar_active) ?>">
                <i class="bi bi-box-seam"></i> Relief Goods
            </a>
            <a href="distributions.php" class="<?= admin_sidebar_item_class('distributions', $sidebar_active) ?>">
                <i class="bi bi-arrow-left-right"></i> Distributions
            </a>
            <a href="requests.php#pending" class="<?= admin_sidebar_item_class('requests', $sidebar_active) ?>" data-pending-requests-link>
                <i class="bi bi-chat-dots"></i> Requests
                <span class="badge bg-danger ms-2" data-pending-requests-badge<?= $sidebar_pending_requests > 0 ? '' : ' hidden' ?>><?= (int) $sidebar_pending_requests ?></span>
            </a>
            <a href="business-chat.php" class="<?= admin_sidebar_item_class('business-chat', $sidebar_active) ?>">
                <i class="bi bi-chat-square-text"></i> Business Chat
            </a>
            <a href="reports.php" class="<?= admin_sidebar_item_class('reports', $sidebar_active) ?>">
                <i class="bi bi-file-earmark-text"></i> Reports
            </a>
            <a href="users.php" class="<?= admin_sidebar_item_class('users', $sidebar_active) ?>">
                <i class="bi bi-person-check"></i> Users
            </a>
            <a href="settings.php" class="<?= admin_sidebar_item_class('settings', $sidebar_active) ?>">
                <i class="bi bi-gear"></i> Settings
            </a>
        </nav>
    </div>
