<?php
session_start();
require_once __DIR__ . DIRECTORY_SEPARATOR . 'db.php';
header('Content-Type: application/json; charset=utf-8');

function respond($success, $message, $redirect = 'dashboard.php')
{
    http_response_code($success ? 200 : 400);
    echo json_encode(['success' => $success, 'message' => $message, 'redirect' => $redirect]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Only POST requests are accepted.');
}

$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$action = $input['action'] ?? 'login';
$role = $input['role'] ?? '';
$username = trim((string) ($input['username'] ?? ''));
$password = (string) ($input['password'] ?? '');
$confirmPassword = (string) ($input['confirm_password'] ?? '');
$name = trim((string) ($input['name'] ?? ''));
$roles = ['admin', 'client', 'assistant'];

if (!in_array($role, $roles, true) || $username === '' || $password === '') {
    respond(false, 'Please provide a valid role, username, and password.');
}

if ($role === 'admin') {
    if ($action !== 'login' || $username !== 'admin' || $password !== 'admin123') {
        respond(false, 'Incorrect admin username or password.');
    }
    $_SESSION['user'] = ['role' => 'admin', 'name' => 'Administrator', 'username' => 'admin'];
    respond(true, 'Admin signed in successfully.', 'dashboard.php');
}

$roleRedirect = $role === 'client' ? 'client.php' : 'dashboard.php';
$database = db();

if ($action === 'register') {
    if ($name === '') {
        respond(false, 'Please enter your full name.');
    }
    if (strlen($password) < 8 || $password !== $confirmPassword) {
        respond(false, 'Use a password of at least 8 characters and confirm it correctly.');
    }
    $existing = $database->prepare('SELECT id FROM users WHERE username = ?');
    $existing->execute([$username]);
    if ($existing->fetch()) {
        respond(false, 'That username is already registered.');
    }
    $statement = $database->prepare('INSERT INTO users (name, username, role, password_hash, created_at) VALUES (?, ?, ?, ?, ?)');
    $statement->execute([$name, $username, $role, password_hash($password, PASSWORD_DEFAULT), date('Y-m-d H:i:s')]);
    $_SESSION['user'] = ['role' => $role, 'name' => $name, 'username' => $username];
    respond(true, 'Account created successfully.', $roleRedirect);
}

$statement = $database->prepare('SELECT name, username, role, password_hash FROM users WHERE username = ? AND role = ?');
$statement->execute([$username, $role]);
$account = $statement->fetch();
if ($account && password_verify($password, $account['password_hash'])) {
    $_SESSION['user'] = ['role' => $role, 'name' => $account['name'], 'username' => $username];
    respond(true, 'Signed in successfully.', $roleRedirect);
}

respond(false, "No matching {$role} account found.");
