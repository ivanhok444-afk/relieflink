<?php require_once __DIR__ . '/../../config/SessionHandler.php'; AppSessionHandler::startSession(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin</title>
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
    <style>
        .user-table { min-width: 1200px; table-layout: fixed; }
        .user-table th, .user-table td { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .user-location .bi { color: var(--app-muted); margin-right: 4px; }
        .user-location-empty { color: var(--app-muted); }
        .user-status { align-items: center; display: inline-flex; height: 24px; justify-content: center; min-width: 76px; }
        .user-actions { align-items: center; display: flex; flex-wrap: nowrap; gap: 6px; }
        .user-actions form { display: inline-flex; margin: 0; }
        .user-actions .btn { align-items: center; display: inline-flex; flex: 0 0 auto; height: 36px; justify-content: center; padding: 0 10px; white-space: nowrap; }
        .user-actions .user-edit { width: 76px; }
        .user-actions .user-toggle { padding: 0; width: 40px; }
        .users-toolbar { align-items: center; display: flex; flex-wrap: wrap; gap: 12px; justify-content: space-between; margin-bottom: 16px; }
        .users-search { max-width: 460px; position: relative; width: 100%; }
        .users-search > .bi-search { color: var(--app-muted); left: 13px; pointer-events: none; position: absolute; top: 50%; transform: translateY(-50%); }
        .users-search .form-control { padding-left: 38px; padding-right: 44px; }
        .users-search-clear { align-items: center; background: transparent; border: 0; color: var(--app-muted); display: inline-flex; height: 36px; justify-content: center; position: absolute; right: 4px; top: 3px; width: 36px; }
        .users-search-clear:hover { color: var(--app-ink); }
        .users-result-count { color: var(--app-muted); font-size: 12px; white-space: nowrap; }
        .users-empty-state { color: var(--app-muted); display: none; padding: 24px 12px; text-align: center; }
    </style>
</head>
<body>
    <?php
    require_once __DIR__ . '/../../config/SessionHandler.php';
    AppSessionHandler::requireAdmin();

    require_once __DIR__ . '/../../models/User.php';

    $user_model = new User();
    $users = $user_model->getAllUsers();
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
                <i class="bi bi-person-check"></i> User Management
            </div>
            <div class="d-flex gap-2">
                <a href="users-by-location.php" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-geo-alt"></i> Accounts by location
                </a>
                <a href="user-create.php" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> Add New User
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-list"></i> User List
            </div>
            <div class="card-body">
                <div class="users-toolbar">
                    <div class="users-search">
                        <label class="visually-hidden" for="users-search">Search users</label>
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <input class="form-control" id="users-search" type="search" placeholder="Search username, name, email, location, role, or status" autocomplete="off">
                        <button class="users-search-clear" id="users-search-clear" type="button" aria-label="Clear search" title="Clear search" hidden>
                            <i class="bi bi-x-lg" aria-hidden="true"></i>
                        </button>
                    </div>
                    <span class="users-result-count" id="users-result-count" aria-live="polite">Showing <?php echo count($users); ?> users</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover user-table">
                        <colgroup>
                            <col style="width: 13%;">
                            <col style="width: 18%;">
                            <col style="width: 14%;">
                            <col style="width: 17%;">
                            <col style="width: 7%;">
                            <col style="width: 8%;">
                            <col style="width: 11%;">
                            <col style="width: 12%;">
                        </colgroup>
                        <thead class="table-light">
                            <tr>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Name</th>
                                <th>Location</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                            <tr>
                                <td><strong><?php echo $u['username']; ?></strong></td>
                                <td><?php echo $u['email']; ?></td>
                                <td><?php echo $u['first_name'] . ' ' . $u['last_name']; ?></td>
                                <td>
                                    <?php if (!empty($u['location'])): ?>
                                        <span class="user-location" title="<?php echo htmlspecialchars($u['location'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <i class="bi bi-geo-alt" aria-hidden="true"></i>
                                            <?php echo htmlspecialchars($u['location'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="user-location-empty">Not set</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-info"><?php echo ucfirst($u['role']); ?></span></td>
                                <td>
                                    <?php if ($u['is_active']): ?>
                                        <span class="badge bg-success user-status">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger user-status">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="user-joined"><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                <td>
                                    <div class="user-actions">
                                        <a href="user-edit.php?id=<?php echo $u['id']; ?>" class="btn btn-sm btn-warning user-edit">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <form action="<?= APP_BASE_PATH ?>/controllers/UserController.php" method="POST">
                                            <?= AppSessionHandler::csrfField() ?>
                                            <input type="hidden" name="action" value="toggle">
                                            <input type="hidden" name="id" value="<?php echo (int) $u['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-<?php echo $u['is_active'] ? 'danger' : 'success'; ?> user-toggle" aria-label="<?php echo $u['is_active'] ? 'Deactivate' : 'Activate'; ?> <?php echo htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8'); ?>" title="<?php echo $u['is_active'] ? 'Deactivate user' : 'Activate user'; ?>">
                                                <i class="bi bi-toggle-<?php echo $u['is_active'] ? 'on' : 'off'; ?>"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p class="users-empty-state" id="users-empty-state">No users match your search.</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const usersSearch = document.getElementById('users-search');
        const usersSearchClear = document.getElementById('users-search-clear');
        const userRows = [...document.querySelectorAll('.user-table tbody tr')];
        const usersResultCount = document.getElementById('users-result-count');
        const usersEmptyState = document.getElementById('users-empty-state');

        function filterUsers() {
            const query = usersSearch.value.trim().toLocaleLowerCase();
            let visibleCount = 0;

            userRows.forEach((row) => {
                const matches = row.textContent.toLocaleLowerCase().includes(query);
                row.hidden = !matches;
                if (matches) visibleCount++;
            });

            usersSearchClear.hidden = query.length === 0;
            usersResultCount.textContent = query
                ? `Showing ${visibleCount} of ${userRows.length} users`
                : `Showing ${userRows.length} users`;
            usersEmptyState.style.display = visibleCount === 0 ? 'block' : 'none';
        }

        usersSearch.addEventListener('input', filterUsers);
        usersSearchClear.addEventListener('click', () => {
            usersSearch.value = '';
            filterUsers();
            usersSearch.focus();
        });
    </script>
</body>
</html>
