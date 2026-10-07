<?php
require __DIR__ . '/app.php';

if (isset($_SESSION['user'])) {
    header('Location: welcome.php', true, 303);
    exit;
}
$errors = [];
$values = ['email' => ''];
$message = $_SESSION['success'] ?? '';
unset($_SESSION['success']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['email'] = input_value($_POST, 'email');
    $errors = validate_login($_POST);
    if (!valid_csrf($_POST['csrf_token'] ?? null)) {
        $errors['form'] = 'Your form has expired. Please try again.';
        http_response_code(400);
    } elseif ($errors === []) {
        try {
            $user = authenticate($values['email'], input_value($_POST, 'password', false));
            if ($user !== null) {
                session_regenerate_id(true);
                $_SESSION['user'] = $user;
                // Rotate the form token when authentication state changes.
                unset($_SESSION['csrf_token']);
                header('Location: welcome.php', true, 303);
                exit;
            }
            $errors['form'] = 'Email or password is incorrect. Please try again.';
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            $errors['form'] = 'We could not sign you in right now. Please try again.';
        }
    }
    if ($errors !== [] && http_response_code() !== 400) {
        http_response_code(422);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>
<body>
<main class="card">
    <h1>Welcome Back</h1>
    <p class="subtitle">Sign in to continue to your account.</p>
    <?php if ($message !== ''): ?>
        <p class="success" role="status"><?= escape($message) ?></p>
    <?php endif; ?>
    <?php if ($errors !== []): ?>
        <p class="error-message" role="alert"><?= escape($errors['form'] ?? 'Please check the fields below.') ?></p>
    <?php endif; ?>

    <form action="index.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
        <div class="field">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" placeholder="Enter your email"
                   value="<?= escape($values['email']) ?>" autocomplete="email" maxlength="254" required
                   aria-describedby="email-error" aria-invalid="<?= isset($errors['email']) ? 'true' : 'false' ?>">
            <p class="error" id="email-error"><?= escape($errors['email'] ?? '') ?></p>
        </div>
        <div class="field">
            <label for="password">Password</label>
            <div class="password-field">
                <input type="password" id="password" name="password" placeholder="Enter your password"
                       autocomplete="current-password" pattern="[\s\S]{1,72}" title="Use no more than 72 characters."
                       required aria-describedby="password-error"
                       aria-invalid="<?= isset($errors['password']) ? 'true' : 'false' ?>">
                <button type="button" class="password-toggle" aria-controls="password"
                        aria-label="Show password" aria-pressed="false" hidden>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                        <circle cx="12" cy="12" r="3"/>
                        <path class="eye-slash" d="m3 3 18 18"/>
                    </svg>
                </button>
            </div>
            <p class="error" id="password-error"><?= escape($errors['password'] ?? '') ?></p>
        </div>
        <button type="submit" class="submit-button">Sign In</button>
    </form>
    <p class="footer">Don't have an account? <a href="register.php">Register</a></p>
</main>
</body>
</html>
