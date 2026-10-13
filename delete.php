<?php
require_once 'config.php';
checkAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file'])) {
    $filename = basename($_POST['file']);
    $userDir = getUserUploadDir($_SESSION['user']);
    $filePath = realpath($userDir . $filename);
    $realUserDir = realpath($userDir);

    // Verificare autorizare și izolare fișiere
    if ($filePath && $realUserDir && strpos($filePath, $realUserDir) === 0 && file_exists($filePath)) {
        unlink($filePath);
    }
}

header('Location: index.php');
exit;