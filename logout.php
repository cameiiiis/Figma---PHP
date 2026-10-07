<?php
require __DIR__ . '/app.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Please use the Sign Out button to sign out.');
}
if (!valid_csrf($_POST['csrf_token'] ?? null)) {
    http_response_code(400);
    exit('Your form has expired. Return to your account and try signing out again.');
}
$_SESSION = [];
session_regenerate_id(true);
$_SESSION['success'] = 'You have signed out successfully.';
header('Location: index.php', true, 303);
exit;
