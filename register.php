<?php
require __DIR__ . '/app.php';

if (isset($_SESSION['user'])) {
    header('Location: welcome.php', true, 303);
    exit;
}
$errors = [];
$values = ['name' => '', 'email' => ''];
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values = ['name' => input_value($_POST, 'name'), 'email' => input_value($_POST, 'email')];
    $errors = validate_registration($_POST);
    if (!valid_csrf($_POST['csrf_token'] ?? null)) {
        $errors['form'] = 'Your form has expired. Please try again.';
        http_response_code(400);
    } elseif ($errors === []) {
        try {
            if (register_user($values['name'], $values['email'], input_value($_POST, 'password', false))) {
                $_SESSION['success'] = 'Account created successfully! You can now sign in.';
                header('Location: index.php', true, 303);
                exit;
            }
            $errors['email'] = 'This email is already registered. Sign in or use a different email.';
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            $errors['form'] = 'We could not create your account. Please try again.';
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
    <title>Registration</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>
<body>
<main class="card">
    <h1>Create Account</h1>
    <p class="subtitle">Get started by creating your account.</p>
    <?php if ($errors !== []): ?>
        <p class="error-message" role="alert"><?= escape($errors['form'] ?? 'Please check the fields below.') ?></p>
    <?php endif; ?>

    <form action="register.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
        <div class="field">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" placeholder="Enter your full name"
                   value="<?= escape($values['name']) ?>" autocomplete="name" minlength="2" maxlength="80" required
                   aria-describedby="name-error" aria-invalid="<?= isset($errors['name']) ? 'true' : 'false' ?>">
            <p class="error" id="name-error"><?= escape($errors['name'] ?? '') ?></p>
        </div>
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
                <input type="password" id="password" name="password" placeholder="Create a strong password"
                       autocomplete="new-password" required
                       pattern="(?=[\s\S]*[a-z])(?=[\s\S]*[A-Z])(?=[\s\S]*[0-9])[\s\S]{8,72}"
                       title="Use 8–72 characters with uppercase, lowercase, and a number."
                       aria-describedby="password-hint password-error"
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
            <p class="hint" id="password-hint">8–72 characters · Uppercase, lowercase &amp; a number</p>
            <p class="error" id="password-error"><?= escape($errors['password'] ?? '') ?></p>
        </div>
        <div class="field">
            <label for="confirm_password">Confirm Password</label>
            <div class="password-field">
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password"
                       autocomplete="new-password" pattern="[\s\S]{8,72}" title="Use 8–72 characters." required
                       aria-describedby="confirm_password-error"
                       aria-invalid="<?= isset($errors['confirm_password']) ? 'true' : 'false' ?>">
                <button type="button" class="password-toggle" aria-controls="confirm_password"
                        aria-label="Show confirm password" aria-pressed="false" hidden>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                        <circle cx="12" cy="12" r="3"/>
                        <path class="eye-slash" d="m3 3 18 18"/>
                    </svg>
                </button>
            </div>
            <p class="error" id="confirm_password-error"><?= escape($errors['confirm_password'] ?? '') ?></p>
        </div>
        <button type="submit" class="submit-button">Create Account</button>
    </form>
    <p class="footer">Already have an account? <a href="index.php">Sign In</a></p>
</main>
</body>
</html>
