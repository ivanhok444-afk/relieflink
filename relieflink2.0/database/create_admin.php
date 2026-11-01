<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../config/Database.php';

function promptAdminValue($prompt) {
    fwrite(STDOUT, $prompt);
    return trim((string) fgets(STDIN));
}

$currentUsername = promptAdminValue('Existing admin username to replace: ');
$username = promptAdminValue('New admin username: ');
$email = promptAdminValue('Admin email: ');
$firstName = promptAdminValue('First name: ');
$lastName = promptAdminValue('Last name: ');
$password = promptAdminValue('New password (minimum 12 characters; input is visible): ');

if ($currentUsername === '' || $username === '' || $firstName === '' || $lastName === ''
    || !filter_var($email, FILTER_VALIDATE_EMAIL)
    || strlen($password) < 12) {
    fwrite(STDERR, "Provide all account details, a valid email, and a password of at least 12 characters.\n");
    exit(1);
}

$connection = (new Database())->connect();
$connection->beginTransaction();

try {
    $findUser = $connection->prepare(
        "SELECT id FROM users WHERE username = ? AND role = 'admin' LIMIT 1 FOR UPDATE"
    );
    $findUser->execute([$currentUsername]);
    $userId = $findUser->fetchColumn();
    if (!$userId) {
        throw new RuntimeException('The specified existing admin account was not found.');
    }

    $findConflict = $connection->prepare(
        'SELECT id FROM users WHERE (username = ? OR email = ?) AND id <> ? LIMIT 1'
    );
    $findConflict->execute([$username, $email, $userId]);
    if ($findConflict->fetchColumn()) {
        throw new RuntimeException('The new username or email is already in use by another account.');
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $saveUser = $connection->prepare(
        "UPDATE users SET username = ?, email = ?, password = ?, first_name = ?, last_name = ?, is_active = 1
         WHERE id = ? AND role = 'admin'"
    );
    $saveUser->execute([$username, $email, $hashedPassword, $firstName, $lastName, $userId]);

    $connection->commit();
    fwrite(STDOUT, "Administrator account replaced successfully.\n");
} catch (Throwable $error) {
    if ($connection->inTransaction()) {
        $connection->rollBack();
    }
    fwrite(STDERR, 'Unable to save administrator account: ' . $error->getMessage() . "\n");
    exit(1);
}
