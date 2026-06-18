<?php
session_start();
include 'dbConnection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

$username = trim((string)($_POST['username'] ?? ''));
$password = (string)($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    $_SESSION['error'] = 'Please enter both username and password.';
    header("Location: login.php");
    exit();
}

// Verify user credentials. Allow login by username or email for compatibility.
$stmt = $db->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
if (!$stmt) {
    $_SESSION['error'] = 'Unable to process login at this time.';
    header("Location: login.php");
    exit();
}

$stmt->bind_param("ss", $username, $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    error_log('Auth failed: user not found for identifier=' . $username);
}

$isPasswordValid = false;
if ($user) {
    // Support both current and legacy column naming.
    $storedPasswordHash = (string)($user['password_hash'] ?? ($user['password'] ?? ''));
    $isPasswordValid = password_verify($password, $storedPasswordHash);

    // Backward compatibility for legacy plaintext passwords, then migrate to hash.
    if (!$isPasswordValid && $storedPasswordHash !== '' && hash_equals($storedPasswordHash, $password)) {
        $isPasswordValid = true;
    }

    // Additional legacy compatibility for md5/sha1 password storage.
    if (!$isPasswordValid && preg_match('/^[a-f0-9]{32}$/i', $storedPasswordHash)) {
        $isPasswordValid = hash_equals(strtolower($storedPasswordHash), md5($password));
    }
    if (!$isPasswordValid && preg_match('/^[a-f0-9]{40}$/i', $storedPasswordHash)) {
        $isPasswordValid = hash_equals(strtolower($storedPasswordHash), sha1($password));
    }

    // Migrate any accepted legacy format to PASSWORD_DEFAULT.
    if ($isPasswordValid && !password_get_info($storedPasswordHash)['algo']) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        if ($newHash !== false && isset($user['username'])) {
            $updateStmt = $db->prepare("UPDATE users SET password_hash = ? WHERE username = ?");
            if ($updateStmt) {
                $updateStmt->bind_param("ss", $newHash, $user['username']);
                $updateStmt->execute();
                $updateStmt->close();
            }
        }
    }

    if (!$isPasswordValid) {
        error_log('Auth failed: password mismatch for username=' . (string)($user['username'] ?? 'unknown') . ', hash_prefix=' . substr($storedPasswordHash, 0, 8));
    }
}

if ($user && $isPasswordValid) {
    $resolvedUserId = $user['user_id'] ?? ($user['id'] ?? ($user['users_id'] ?? null));
    if ($resolvedUserId === null || $resolvedUserId === '') {
        $_SESSION['error'] = 'Login failed due to account configuration.';
        header("Location: login.php");
        exit();
    }

    $_SESSION['user_id'] = (int)$resolvedUserId;
    $_SESSION['username'] = $user['username'];
    header("Location: index.php");
    exit();
} else {
    if (!$user) {
        $_SESSION['error'] = "Invalid credentials: User '$username' not found in database.";
    } else {
        $_SESSION['error'] = "Invalid credentials: Password incorrect for user '$username'.";
    }
    header("Location: login.php");
    exit();
}
?>
