<?php require_once __DIR__ . '/../../config/SessionHandler.php'; AppSessionHandler::startSession(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Family Member - Admin</title>
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
        .sidebar-nav .nav-link {
            color: #333;
            padding: 12px 20px;
            border-left: 4px solid transparent;
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
    AppSessionHandler::requireAdmin();

    require_once __DIR__ . '/../../models/FamilyMember.php';
    require_once __DIR__ . '/../../models/Household.php';

    $member = new FamilyMember();
    $household = new Household();
    $id = $_GET['id'] ?? 0;
    $member_data = $member->getMemberById($id);

    if (!$member_data) {
        header('Location: ' . APP_BASE_PATH . '/views/admin/households.php');
        exit;
    }

    $household_data = $household->getHouseholdById($member_data['household_id']);
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
        <div class="page-title" style="margin-bottom: 30px;">
            <i class="bi bi-pencil"></i> Edit Family Member
        </div>

        <div class="card">
            <div class="card-header bg-warning text-white">
                <i class="bi bi-people"></i> Edit Family Member Information
            </div>
            <div class="card-body">
                <form action="<?= APP_BASE_PATH ?>/controllers/FamilyMemberController.php" method="POST">
                    <?= AppSessionHandler::csrfField() ?>
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?php echo $member_data['id']; ?>">
                    <input type="hidden" name="household_id" value="<?php echo $member_data['household_id']; ?>">

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="first_name" required value="<?php echo $member_data['first_name']; ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Middle Name</label>
                                <input type="text" class="form-control" name="middle_name" value="<?php echo $member_data['middle_name']; ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="last_name" required value="<?php echo $member_data['last_name']; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Gender</label>
                                <select class="form-select" name="gender">
                                    <option value="">Select Gender</option>
                                    <option value="Male" <?php echo $member_data['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo $member_data['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Age</label>
                                <input type="number" class="form-control" name="age" value="<?php echo $member_data['age']; ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" name="date_of_birth" value="<?php echo $member_data['date_of_birth']; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Relationship</label>
                                <select class="form-select" name="relationship">
                                    <option value="">Select Relationship</option>
                                    <option value="Spouse" <?php echo $member_data['relationship'] == 'Spouse' ? 'selected' : ''; ?>>Spouse</option>
                                    <option value="Son" <?php echo $member_data['relationship'] == 'Son' ? 'selected' : ''; ?>>Son</option>
                                    <option value="Daughter" <?php echo $member_data['relationship'] == 'Daughter' ? 'selected' : ''; ?>>Daughter</option>
                                    <option value="Father" <?php echo $member_data['relationship'] == 'Father' ? 'selected' : ''; ?>>Father</option>
                                    <option value="Mother" <?php echo $member_data['relationship'] == 'Mother' ? 'selected' : ''; ?>>Mother</option>
                                    <option value="Brother" <?php echo $member_data['relationship'] == 'Brother' ? 'selected' : ''; ?>>Brother</option>
                                    <option value="Sister" <?php echo $member_data['relationship'] == 'Sister' ? 'selected' : ''; ?>>Sister</option>
                                    <option value="Grandparent" <?php echo $member_data['relationship'] == 'Grandparent' ? 'selected' : ''; ?>>Grandparent</option>
                                    <option value="Other" <?php echo $member_data['relationship'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Special Status</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_senior" id="is_senior" <?php echo $member_data['is_senior'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_senior">Senior Citizen</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_pwd" id="is_pwd" <?php echo $member_data['is_pwd'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_pwd">Person with Disability (PWD)</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <div>
                            <a href="household-detail.php?id=<?php echo $member_data['household_id']; ?>" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Back
                            </a>
                            <button type="button" class="btn btn-danger" onclick="deleteMember(<?php echo $member_data['id']; ?>, <?php echo $member_data['household_id']; ?>)">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        </div>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-check-circle"></i> Update Member
                        </button>
                    </div>
                </form>

                <form id="deleteForm" action="<?= APP_BASE_PATH ?>/controllers/FamilyMemberController.php" method="POST" style="display: none;">
                    <?= AppSessionHandler::csrfField() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="deleteId">
                    <input type="hidden" name="household_id" id="deleteHouseholdId">
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function deleteMember(id, household_id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('deleteId').value = id;
                    document.getElementById('deleteHouseholdId').value = household_id;
                    document.getElementById('deleteForm').submit();
                }
            });
        }
    </script>
</body>
</html>
