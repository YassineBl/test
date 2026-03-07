<?php
// Admin Authentication & Session Management

// Configure sessions to work properly
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');

// Avoid collisions with other localhost PHP apps using the default PHPSESSID.
session_name('OMLPARA_ADMIN_SESSID');
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']);
}

function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        // redirect relatively so it works regardless of project root
        redirect('login.php');
    }
}

function login($username, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare('SELECT id, username, password FROM admin_users WHERE username = ?');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    
    if ($admin) {
        // if the password is already hashed (normal case)
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            return true;
        }

        // legacy record might store plaintext password; check and migrate
        if ($password === $admin['password']) {
            // rehash and update the database so next time it works with password_verify
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $update = $pdo->prepare('UPDATE admin_users SET password = ? WHERE id = ?');
            $update->execute([$newHash, $admin['id']]);

            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            return true;
        }
    }
    return false;
}

function logout() {
    session_destroy();
    redirect('login.php');
}

function verifyAdminUser($username, $password) {
    return login($username, $password);
}
?>
