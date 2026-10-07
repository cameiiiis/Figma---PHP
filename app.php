<?php
// Functions shared by the login and registration pages.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('php_lab_auth');
    session_start([
        'use_strict_mode' => true,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
}
if (PHP_SAPI !== 'cli') {
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
    header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self'; font-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'self'");
}

// Read text from a form. Passwords should not have their spaces removed.
function input_value($data, $key, $trim = true)
{
    $value = isset($data[$key]) && is_string($data[$key]) ? $data[$key] : '';
    return $trim ? trim($value) : $value;
}

// Display user input safely in HTML.
function escape($value)
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function validate_login($data)
{
    $errors = [];
    $email = input_value($data, 'email');
    $password = input_value($data, 'password', false);
    if ($email === '') {
        $errors['email'] = 'Please enter your email address.';
    } elseif (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid email address, like you@example.com.';
    }
    if ($password === '') {
        $errors['password'] = 'Please enter your password.';
    } elseif (!mb_check_encoding($password, 'UTF-8') || str_contains($password, "\0")) {
        $errors['password'] = 'Your password contains an unsupported character. Please use a different password.';
    } elseif (mb_strlen($password, 'UTF-8') > 72) {
        $errors['password'] = 'Your password must be no more than 72 characters.';
    }
    return $errors;
}

function validate_registration($data)
{
    $errors = validate_login($data);
    $name = input_value($data, 'name');
    $password = input_value($data, 'password', false);
    $confirmation = input_value($data, 'confirm_password', false);
    if ($name === '') {
        $errors['name'] = 'Please enter your full name.';
    } elseif (mb_strlen($name) < 2 || mb_strlen($name) > 80) {
        $errors['name'] = 'Your name must be between 2 and 80 characters.';
    }
    if ($password !== '' && !isset($errors['password']) &&
        (mb_strlen($password, 'UTF-8') < 8 || !preg_match('/[A-Z]/', $password) ||
         !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password))) {
        $errors['password'] = 'Use at least 8 characters with uppercase, lowercase, and a number.';
    }
    if ($confirmation === '') {
        $errors['confirm_password'] = 'Please confirm your password.';
    } elseif ($confirmation !== $password) {
        $errors['confirm_password'] = 'Your passwords do not match. Please try again.';
    }
    return $errors;
}

// Create the account database the first time it is used.
function db()
{
    static $database = null;
    if ($database === null) {
        $path = getenv('AUTH_DB_PATH') ?: __DIR__ . '/storage/users.sqlite';
        $database = new PDO('sqlite:' . $path, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $database->exec('PRAGMA busy_timeout = 5000');
        $database->exec('CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )');
    }
    return $database;
}

function register_user($name, $email, $password)
{
    try {
        $statement = db()->prepare('INSERT INTO users (name, email, password_hash) VALUES (:name, :email, :password_hash)');
        $statement->execute([
            'name' => trim($name),
            'email' => strtolower(trim($email)),
            'password_hash' => password_hash($password, PASSWORD_ARGON2ID),
        ]);
        return true;
    } catch (PDOException $exception) {
        // A unique-email collision is an expected validation failure.
        if (($exception->errorInfo[1] ?? null) === 19) {
            return false;
        }
        throw $exception;
    }
}

function authenticate($email, $password)
{
    $statement = db()->prepare('SELECT id, name, email, password_hash FROM users WHERE email = :email');
    $statement->execute(['email' => strtolower(trim($email))]);
    $user = $statement->fetch();
    if (!$user || !mb_check_encoding($password, 'UTF-8') || mb_strlen($password, 'UTF-8') > 72 || str_contains($password, "\0")) {
        return null;
    }
    // Keep old bcrypt accounts working without accepting a truncated password.
    if (password_get_info($user['password_hash'])['algoName'] === 'bcrypt' && strlen($password) > 72) {
        return null;
    }
    if (!password_verify($password, $user['password_hash'])) {
        return null;
    }
    unset($user['password_hash']);
    return $user;
}

// A random form token helps prevent another website from submitting our forms.
function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function valid_csrf($token)
{
    return is_string($token) && hash_equals(csrf_token(), $token);
}
