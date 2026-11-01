<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../config/Database.php';

$addressData = json_decode(file_get_contents(__DIR__ . '/../assets/data/quirino-address.json'), true);
if (!isset($addressData['municipalities']['Saguday'])) {
    fwrite(STDERR, "Saguday address data is missing.\n");
    exit(1);
}

$connection = (new Database())->connect();
$connection->beginTransaction();

$password = 'SagudayDemo!2026';
$familyNames = [
    'Dela Cruz', 'Santos', 'Reyes', 'Garcia', 'Mendoza',
    'Bautista', 'Gonzales', 'Ramos', 'Aquino', 'Castillo',
    'Cruz', 'Flores', 'Rivera', 'Villanueva', 'Fernandez',
    'Lopez', 'Perez', 'Martinez', 'Gomez', 'Torres',
    'Diaz', 'Morales', 'Javier', 'Navarro', 'Santiago',
    'Domingo', 'Mercado', 'Salazar', 'De Guzman', 'Valdez',
    'Tolentino', 'Espino', 'Manalo', 'Soriano', 'Aguilar',
    'Castro', 'Herrera', 'Padilla', 'Ocampo', 'Panganiban',
    'Luna', 'Rosales', 'Evangelista', 'Magtibay', 'Dizon',
    'Abad', 'Guevarra', 'Lazaro', 'Natividad', 'Ortega'
];
$memberNames = ['Maria', 'Jose', 'Ana', 'Pedro'];
$memberAges = [39, 17, 12, 8];
$memberGenders = ['Female', 'Male', 'Female', 'Male'];
$memberRelationships = ['Spouse', 'Child', 'Child', 'Child'];
$createdAccounts = 0;
$updatedAccounts = 0;
$createdHouseholds = 0;
$createdMembers = 0;
$sampleIndex = 0;

try {
    $findAccount = $connection->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
    $createAccount = $connection->prepare(
        "INSERT INTO users (username, email, password, first_name, last_name, role, is_active)
         VALUES (?, ?, ?, ?, ?, 'user', 1)"
    );
    $updateAccount = $connection->prepare('UPDATE users SET first_name = ?, last_name = ? WHERE id = ?');
    $findHousehold = $connection->prepare('SELECT id, household_id FROM households WHERE user_id = ? LIMIT 1');
    $findHouseholdId = $connection->prepare('SELECT 1 FROM households WHERE household_id = ? LIMIT 1');
    $createHousehold = $connection->prepare(
        'INSERT INTO households
            (user_id, household_name, household_id, street_address, barangay, city, province, postal_code, contact_number, total_members, monthly_income)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $updateHousehold = $connection->prepare(
        'UPDATE households SET household_name = ?, barangay = ?, city = ?, province = ?, total_members = ? WHERE id = ?'
    );
    $countMembers = $connection->prepare('SELECT COUNT(*) FROM family_members WHERE household_id = ?');
    $findMembers = $connection->prepare('SELECT id FROM family_members WHERE household_id = ? ORDER BY id');
    $updateMember = $connection->prepare(
        'UPDATE family_members
         SET first_name = ?, last_name = ?, age = ?, gender = ?, relationship = ?, is_senior = 0, is_pwd = 0
         WHERE id = ?'
    );
    $createMember = $connection->prepare(
        'INSERT INTO family_members (household_id, first_name, last_name, age, gender, relationship, is_senior, is_pwd)
         VALUES (?, ?, ?, ?, ?, ?, 0, 0)'
    );

    foreach ($addressData['municipalities']['Saguday'] as $barangay) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $barangay), '-'));

        for ($index = 1; $index <= 20; $index++) {
            $suffix = str_pad((string) $index, 2, '0', STR_PAD_LEFT);
            $username = 'saguday-' . $slug . '-' . $suffix;
            $email = $username . '@example.invalid';
            $surname = $familyNames[$sampleIndex++ % count($familyNames)];
            $householdName = $surname . ' Family';

            $findAccount->execute([$username]);
            $userId = (int) $findAccount->fetchColumn();
            if ($userId) {
                $updateAccount->execute(['Juan', $surname, $userId]);
                $updatedAccounts++;
            } else {
                $createAccount->execute([
                    $username,
                    $email,
                    password_hash($password, PASSWORD_DEFAULT),
                    'Juan',
                    $surname
                ]);
                $userId = (int) $connection->lastInsertId();
                $createdAccounts++;
            }

            $findHousehold->execute([$userId]);
            $household = $findHousehold->fetch(PDO::FETCH_ASSOC);
            if ($household) {
                $householdId = $household['household_id'];
                $updateHousehold->execute([$householdName, $barangay, 'Saguday', 'Quirino', 5, $household['id']]);
            } else {
                do {
                    $householdId = 'QRN-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(4)));
                    $findHouseholdId->execute([$householdId]);
                    $householdIdExists = (bool) $findHouseholdId->fetchColumn();
                } while ($householdIdExists);

                $createHousehold->execute([
                    $userId,
                    $householdName,
                    $householdId,
                    'Sample address',
                    $barangay,
                    'Saguday',
                    'Quirino',
                    '',
                    '',
                    5,
                    0
                ]);
                $household['id'] = (int) $connection->lastInsertId();
                $createdHouseholds++;
            }

            $countMembers->execute([$household['id']]);
            $existingMembers = (int) $countMembers->fetchColumn();
            if ($existingMembers > 4) {
                throw new RuntimeException(
                    'Sample household ' . $householdId . ' already has more than four family-member rows.'
                );
            }

            $findMembers->execute([$household['id']]);
            $memberIds = $findMembers->fetchAll(PDO::FETCH_COLUMN);
            for ($memberIndex = 0; $memberIndex < $existingMembers; $memberIndex++) {
                $updateMember->execute([
                    $memberNames[$memberIndex],
                    $surname,
                    $memberAges[$memberIndex],
                    $memberGenders[$memberIndex],
                    $memberRelationships[$memberIndex],
                    $memberIds[$memberIndex]
                ]);
            }
            for ($memberIndex = $existingMembers; $memberIndex < 4; $memberIndex++) {
                $createMember->execute([
                    $household['id'],
                    $memberNames[$memberIndex],
                    $surname,
                    $memberAges[$memberIndex],
                    $memberGenders[$memberIndex],
                    $memberRelationships[$memberIndex]
                ]);
                $createdMembers++;
            }

        }
    }

    $connection->commit();
} catch (Throwable $error) {
    if ($connection->inTransaction()) {
        $connection->rollBack();
    }
    fwrite(STDERR, 'Seeding failed; transaction rolled back: ' . $error->getMessage() . "\n");
    exit(1);
}

printf("Updated %d accounts; created %d accounts and %d households; added %d family members.\n", $updatedAccounts, $createdAccounts, $createdHouseholds, $createdMembers);
printf("Demo password for newly created users: %s\n", $password);
