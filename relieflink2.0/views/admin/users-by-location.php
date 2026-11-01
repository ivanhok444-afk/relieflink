<?php
require_once __DIR__ . '/../../config/SessionHandler.php';
AppSessionHandler::requireAdmin();

require_once __DIR__ . '/../../config/Database.php';
$database = new Database();
$connection = $database->connect();
$statement = $connection->query(
    "SELECT u.id AS user_id, u.username, u.email, u.first_name, u.last_name, u.is_active,
            h.id AS household_record_id, h.household_name, h.household_id, h.province, h.city, h.barangay
     FROM users u
     LEFT JOIN households h ON h.user_id = u.id
     WHERE u.role <> 'admin'
     ORDER BY h.province, h.city, h.barangay, u.username"
);
$users = $statement->fetchAll(PDO::FETCH_ASSOC);
$addressData = json_decode(file_get_contents(__DIR__ . '/../../assets/data/quirino-address.json'), true);

$locations = [];
foreach ($users as $user) {
    $province = trim($user['province'] ?? '') ?: 'Unassigned province';
    $municipality = trim($user['city'] ?? '') ?: 'Unassigned municipality';
    $barangay = trim($user['barangay'] ?? '') ?: 'Unassigned barangay';
    $locations[$province][$municipality][$barangay][] = $user;
}

foreach ($addressData['municipalities'] as $municipality => $barangays) {
    foreach ($barangays as $barangay) {
        $locations['Quirino'][$municipality][$barangay] ??= [];
    }
}

ksort($locations);
foreach ($locations as &$municipalities) {
    ksort($municipalities);
    foreach ($municipalities as &$barangays) {
        ksort($barangays);
    }
    unset($barangays);
}
unset($municipalities);

$municipalityKpis = [];
$municipalityKpisByKey = [];
$allAccountIds = [];
$allHouseholdIds = [];
$totalBarangays = 0;
foreach ($locations as $province => $municipalities) {
    foreach ($municipalities as $municipality => $barangays) {
        $accountIds = [];
        $householdIds = [];
        foreach ($barangays as $barangayUsers) {
            foreach ($barangayUsers as $barangayUser) {
                $accountIds[$barangayUser['user_id']] = true;
                $allAccountIds[$barangayUser['user_id']] = true;
                if (!empty($barangayUser['household_record_id'])) {
                    $householdIds[$barangayUser['household_record_id']] = true;
                    $allHouseholdIds[$barangayUser['household_record_id']] = true;
                }
            }
        }

        $key = $province . '|' . $municipality;
        $stats = [
            'id' => 'municipality-' . substr(hash('sha256', $key), 0, 12),
            'province' => $province,
            'name' => $municipality,
            'accounts' => count($accountIds),
            'households' => count($householdIds),
            'barangays' => count($barangays)
        ];
        $municipalityKpis[] = $stats;
        $municipalityKpisByKey[$key] = $stats;
        $totalBarangays += count($barangays);
    }
}

$escape = static function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accounts by Location - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/app.css">
    <style>
        .sidebar { left: 0; min-height: 100vh; position: fixed; }
        .location-group { border-bottom: 1px solid var(--app-line); }
        .location-group > summary {
            align-items: center;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            list-style: none;
            padding: 17px 4px;
        }
        .location-group > summary::-webkit-details-marker { display: none; }
        .location-group > summary::after { content: '+'; font-size: 22px; font-weight: 400; }
        .location-group[open] > summary::after { content: '\2212'; }
        .location-title { font: 400 23px/1.2 'DM Serif Display', Georgia, serif; }
        .location-meta { color: var(--app-muted); font-size: 12px; margin-left: auto; margin-right: 16px; }
        .summary-kpis, .municipality-kpi-bar, .municipality-stat-row { display: grid; gap: 10px; }
        .summary-kpis { grid-template-columns: repeat(4, minmax(0, 1fr)); margin: 22px 0 30px; }
        .summary-kpi, .municipality-kpi {
            background: var(--app-surface);
            border: 1px solid var(--app-line);
            border-radius: 4px;
            padding: 15px 17px;
        }
        .summary-kpi span, .municipality-kpi small { color: var(--app-muted); display: block; font-size: 11px; }
        .summary-kpi strong { display: block; font: 400 27px/1.1 'DM Serif Display', Georgia, serif; margin-top: 6px; }
        .municipality-kpi-bar { grid-template-columns: repeat(auto-fit, minmax(175px, 1fr)); margin: 0 0 28px; }
        .municipality-kpi { color: var(--app-ink); cursor: pointer; display: block; text-align: left; width: 100%; }
        .municipality-kpi:hover, .municipality-kpi.active { background: #f2f5e9; border-color: #afc47b; color: var(--app-ink); }
        .municipality-kpi strong { display: block; font-size: 14px; margin: 4px 0; }
        .municipality-kpi small { line-height: 1.45; }
        .location-filters { align-items: end; display: grid; gap: 12px; grid-template-columns: minmax(180px, .7fr) minmax(220px, 1.3fr); margin: 0 0 14px; }
        .location-filters label { display: block; margin-bottom: 5px; }
        .location-filters select, .location-filters input { width: 100%; }
        .location-result-count { color: var(--app-muted); font-size: 12px; margin-bottom: 9px; }
        .account-count { min-width: 45px; }
        .count-track { background: #edf0e8; border-radius: 2px; height: 5px; margin-top: 6px; max-width: 110px; overflow: hidden; }
        .count-track span { background: #9bb85b; display: block; height: 100%; }
        .account-details summary { color: var(--app-ink); cursor: pointer; font-size: 12px; font-weight: 700; white-space: nowrap; }
        .account-list { min-width: 210px; padding-top: 8px; }
        .account-item { border-top: 1px solid var(--app-line); font-size: 11px; padding: 7px 0; }
        .account-item strong, .account-item span { display: block; }
        .account-item span { color: var(--app-muted); overflow-wrap: anywhere; }
        .location-table th, .location-table td { vertical-align: middle; }
        .location-table td:first-child, .location-table td:nth-child(2) { white-space: nowrap; }
        .zero-count { color: #8a938c; }
        .no-location-results { color: var(--app-muted); display: none; padding: 22px; text-align: center; }
        .empty-state { color: var(--app-muted); padding: 24px 0; }
        @media (max-width: 600px) {
            .summary-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .municipality-kpi-bar { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .location-filters { grid-template-columns: 1fr; }
            .location-table { min-width: 700px; }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= APP_BASE_PATH ?>/views/admin/dashboard.php"><i class="bi bi-box-seam"></i> <span class="brand-wordmark">Relief<span class="brand-accent">Link</span></span></a>
            <div class="ms-auto">
                <span class="navbar-text me-3">Welcome, <strong><?= $escape($_SESSION['username'] ?? '') ?></strong></span>
                <a class="btn btn-sm btn-light" href="<?= APP_BASE_PATH ?>/controllers/LogoutController.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
            </div>
        </div>
    </nav>
    <?php require __DIR__ . '/admin_sidebar.php'; ?>
    <main class="main-content">
        <div class="page-title">
            <div><i class="bi bi-geo-alt"></i> Accounts by Location</div>
            <a href="users.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-arrow-left"></i> User list</a>
        </div>
        <p class="text-muted mb-4">Household accounts grouped by province, municipality, and barangay.</p>
        <div class="summary-kpis" aria-label="Location totals">
            <div class="summary-kpi"><span>Municipalities</span><strong><?= count($municipalityKpis) ?></strong></div>
            <div class="summary-kpi"><span>Barangays</span><strong><?= $totalBarangays ?></strong></div>
            <div class="summary-kpi"><span>User accounts</span><strong><?= count($allAccountIds) ?></strong></div>
            <div class="summary-kpi"><span>Households</span><strong><?= count($allHouseholdIds) ?></strong></div>
        </div>
        <h2 class="h5 mb-3">Accounts per municipality</h2>
        <nav class="municipality-kpi-bar" aria-label="Municipality account totals">
            <button class="municipality-kpi active" type="button" data-municipality-filter="">
                <small>All locations</small>
                <strong><?= count($allAccountIds) ?> accounts</strong>
                <small><?= count($allHouseholdIds) ?> households · <?= $totalBarangays ?> barangays</small>
            </button>
            <?php foreach ($municipalityKpis as $stats): ?>
                <button class="municipality-kpi" type="button" data-municipality-filter="<?= $escape($stats['name']) ?>">
                    <small><?= $escape($stats['province']) ?></small>
                    <strong><?= $escape($stats['name']) ?></strong>
                    <small><?= $stats['accounts'] ?> accounts · <?= $stats['households'] ?> households</small>
                </button>
            <?php endforeach; ?>
        </nav>
        <?php if (!$users): ?>
            <p class="empty-state">No user accounts are registered yet. Municipality and barangay totals are shown as zero.</p>
        <?php endif; ?>
        <div class="location-filters">
            <div>
                <label for="municipality-filter">Municipality</label>
                <select class="form-select" id="municipality-filter">
                    <option value="">All municipalities</option>
                    <?php foreach ($municipalityKpis as $stats): ?>
                        <option value="<?= $escape($stats['name']) ?>"><?= $escape($stats['name']) ?>, <?= $escape($stats['province']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="location-search">Search accounts or barangays</label>
                <input class="form-control" id="location-search" type="search" placeholder="Search municipality, barangay, username, or household">
            </div>
        </div>
        <div class="location-result-count" id="location-result-count" aria-live="polite"></div>
        <div class="table-responsive">
            <table class="table location-table">
                <thead>
                    <tr>
                        <th>Municipality</th>
                        <th>Barangay</th>
                        <th>Accounts</th>
                        <th>Households</th>
                        <th>Account details</th>
                    </tr>
                </thead>
                <tbody id="location-table-body">
                    <?php foreach ($locations as $province => $municipalities): ?>
                        <?php foreach ($municipalities as $municipality => $barangays): ?>
                            <?php foreach ($barangays as $barangay => $barangayUsers): ?>
                                <?php
                                $municipalityStats = $municipalityKpisByKey[$province . '|' . $municipality];
                                $barangayAccountIds = [];
                                $barangayHouseholdIds = [];
                                $searchText = $province . ' ' . $municipality . ' ' . $barangay;
                                foreach ($barangayUsers as $barangayUser) {
                                    $barangayAccountIds[$barangayUser['user_id']] = true;
                                    $searchText .= ' ' . $barangayUser['username'] . ' ' . $barangayUser['email'] . ' ' . $barangayUser['household_name'];
                                    if (!empty($barangayUser['household_record_id'])) {
                                        $barangayHouseholdIds[$barangayUser['household_record_id']] = true;
                                    }
                                }
                                $accountCount = count($barangayAccountIds);
                                $barWidth = $accountCount > 0 ? min(100, (int) round($accountCount / max(1, $municipalityStats['accounts']) * 100)) : 0;
                                ?>
                                <tr data-municipality="<?= $escape($municipality) ?>" data-search="<?= $escape(mb_strtolower($searchText, 'UTF-8')) ?>">
                                    <td><strong><?= $escape($municipality) ?></strong><br><small class="text-muted"><?= $escape($province) ?></small></td>
                                    <td><?= $escape($barangay) ?></td>
                                    <td>
                                        <strong class="account-count <?= $accountCount === 0 ? 'zero-count' : '' ?>"><?= $accountCount ?></strong>
                                        <div class="count-track"><span style="width: <?= $barWidth ?>%"></span></div>
                                    </td>
                                    <td class="<?= count($barangayHouseholdIds) === 0 ? 'zero-count' : '' ?>"><?= count($barangayHouseholdIds) ?></td>
                                    <td>
                                        <?php if ($barangayUsers): ?>
                                            <details class="account-details">
                                                <summary>View <?= $accountCount ?> account<?= $accountCount === 1 ? '' : 's' ?></summary>
                                                <div class="account-list">
                                                    <?php foreach ($barangayUsers as $user): ?>
                                                        <div class="account-item">
                                                            <strong><?= $escape(trim($user['first_name'] . ' ' . $user['last_name']) ?: $user['username']) ?></strong>
                                                            <span><?= $escape($user['username']) ?> · <?= $escape($user['email']) ?></span>
                                                            <span><?= $escape($user['household_id'] ?: 'No household linked') ?> · <?= $user['is_active'] ? 'Active' : 'Inactive' ?></span>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </details>
                                        <?php else: ?>
                                            <span class="zero-count">No accounts</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="no-location-results" id="no-location-results">No municipalities or barangays match your filters.</p>
        <script>
            const municipalityFilter = document.getElementById('municipality-filter');
            const locationSearch = document.getElementById('location-search');
            const locationRows = [...document.querySelectorAll('#location-table-body tr')];
            const locationCount = document.getElementById('location-result-count');
            const noLocationResults = document.getElementById('no-location-results');
            const municipalityButtons = [...document.querySelectorAll('[data-municipality-filter]')];

            function filterLocations() {
                const selectedMunicipality = municipalityFilter.value;
                const searchTerm = locationSearch.value.trim().toLocaleLowerCase();
                let visibleRows = 0;

                locationRows.forEach((row) => {
                    const matchesMunicipality = !selectedMunicipality || row.dataset.municipality === selectedMunicipality;
                    const matchesSearch = !searchTerm || row.dataset.search.includes(searchTerm);
                    row.hidden = !matchesMunicipality || !matchesSearch;
                    if (!row.hidden) visibleRows++;
                });

                locationCount.textContent = `Showing ${visibleRows} of ${locationRows.length} barangays`;
                noLocationResults.style.display = visibleRows === 0 ? 'block' : 'none';
            }

            municipalityFilter.addEventListener('change', filterLocations);
            locationSearch.addEventListener('input', filterLocations);
            municipalityButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    municipalityFilter.value = button.dataset.municipalityFilter;
                    municipalityButtons.forEach((item) => item.classList.toggle('active', item === button));
                    filterLocations();
                    document.getElementById('municipality-filter').scrollIntoView({ behavior: 'smooth', block: 'center' });
                });
            });
            filterLocations();
        </script>
    </main>
</body>
</html>