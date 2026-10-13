<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


session_start();

define('USERS_FILE', __DIR__ . '/users.json');
define('UPLOAD_DIR', __DIR__ . '/public/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // Limita 5MB

// Extensii interzise pentru a preveni executarea de scripturi
define('FORBIDDEN_EXTENSIONS', [
    'php', 'phtml', 'php3', 'php4', 'php5', 'phps', 'cgi', 'pl', 
    'exe', 'asp', 'aspx', 'jsp', 'html', 'htm', 'js', 'sh', 'bat'
]);

// Inițializare fișier utilizatori și director încărcări
if (!file_exists(USERS_FILE)) {
    file_put_contents(USERS_FILE, json_encode([], JSON_PRETTY_PRINT));
}

if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

function getUsers(): array {
    $data = file_get_contents(USERS_FILE);
    return json_decode($data, true) ?: [];
}

function saveUsers(array $users): void {
    file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT));
}

function checkAuth(): void {
    if (!isset($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
}

// Returnează calea către directorul privat al utilizatorului
function getUserUploadDir(string $username): string {
    $cleanUser = preg_replace('/[^a-zA-Z0-9_-]/', '', $username);
    $dir = UPLOAD_DIR . $cleanUser . '/';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    return $dir;
}