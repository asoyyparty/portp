<?php
// admin/auth.php - Authentication & Security Handler

if (session_status() === PHP_SESSION_NONE) {
    // Configure secure session parameters
    session_set_cookie_params([
        'lifetime' => 86400 * 7, // 7 days
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

define('DATA_DIR', dirname(__DIR__) . '/data');
define('USERS_FILE', DATA_DIR . '/users.php');
define('CONTENT_FILE', DATA_DIR . '/content.json');

// Get all users from secure users.php
function get_users() {
    if (!file_exists(USERS_FILE)) {
        return ['users' => []];
    }
    $raw = file_get_contents(USERS_FILE);
    $json = preg_replace('/^<\?php\s+exit;\s*\?>\s*/i', '', $raw);
    $data = json_decode($json, true);
    return is_array($data) ? $data : ['users' => []];
}

// Check if user is currently logged in
function is_authenticated() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['username']);
}

// Require authentication guard
function require_auth() {
    if (!is_authenticated()) {
        header('Location: login.php');
        exit;
    }
}

// Attempt login
function attempt_login($username, $password, &$error = null) {
    // Basic rate limit check
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt_time'] = time();
    }

    if ($_SESSION['login_attempts'] >= 5) {
        $lockout = 300 - (time() - $_SESSION['last_attempt_time']);
        if ($lockout > 0) {
            $error = "Terlalu banyak percobaan gagal. Silakan coba lagi dalam " . ceil($lockout / 60) . " menit.";
            return false;
        } else {
            $_SESSION['login_attempts'] = 0;
        }
    }

    $usersData = get_users();
    $username = trim($username);

    foreach ($usersData['users'] as $user) {
        if (strtolower($user['username']) === strtolower($username)) {
            if (password_verify($password, $user['password'])) {
                // Success
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_name'] = $user['name'] ?? $user['username'];
                $_SESSION['user_role'] = $user['role'] ?? 'Admin';
                $_SESSION['login_attempts'] = 0;
                session_regenerate_id(true);
                return true;
            }
        }
    }

    $_SESSION['login_attempts']++;
    $_SESSION['last_attempt_time'] = time();
    $remaining = max(0, 5 - $_SESSION['login_attempts']);
    $error = "Username atau password salah. (Sisa percobaan: $remaining)";
    return false;
}

// Logout
function logout_user() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

// Update password
function update_password($userId, $newPassword) {
    $usersData = get_users();
    $updated = false;
    foreach ($usersData['users'] as &$user) {
        if ($user['id'] == $userId) {
            $user['password'] = password_hash($newPassword, PASSWORD_BCRYPT);
            $user['updated_at'] = date('Y-m-d H:i:s');
            $updated = true;
            break;
        }
    }
    if ($updated) {
        $json = json_encode($usersData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents(USERS_FILE, "<?php exit; ?>\n" . $json);
        return true;
    }
    return false;
}

// CSRF Protection
function get_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token ?? '');
}

// Flash Message helper
function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
