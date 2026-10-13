<?php
require_once 'config.php';
checkAuth();

if (isset($_GET['file'])) {
    $filename = basename($_GET['file']);
    $userDir = getUserUploadDir($_SESSION['user']);
    $filePath = realpath($userDir . $filename);
    $realUserDir = realpath($userDir);

    // Verificare securitate: fișierul există și se află strict în folderul propriu
    if ($filePath && $realUserDir && strpos($filePath, $realUserDir) === 0 && file_exists($filePath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }
}

header('Location: index.php');
exit;