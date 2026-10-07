<?php
require __DIR__ . '/app.php';
if (!isset($_SESSION['user'])) {
    header('Location: index.php', true, 303);
    exit;
}
$user = $_SESSION['user'];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#007e9f">
    <title>You're signed in | Account</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="card" aria-labelledby="page-title">
    <header>
        <h1 id="page-title">Welcome, <?= escape($user['name']) ?>!</h1>
        <p class="subtitle">You're successfully signed in to your account.</p>
    </header>
    <dl class="account-details">
        <dt>Email Address</dt>
        <dd><?= escape($user['email']) ?></dd>
    </dl>
    <form method="post" action="logout.php">
        <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
        <button class="submit-button" type="submit">Sign Out</button>
    </form>
</main>
</body>
</html>
