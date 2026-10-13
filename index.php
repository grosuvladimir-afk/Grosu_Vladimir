<?php
require_once 'config.php';
checkAuth();

$message = '';
$error = '';

// Procesare încărcare fișier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Eroare la încărcarea fișierului.';
    } elseif ($file['size'] > MAX_FILE_SIZE) {
        $error = 'Fișierul depășește dimensiunea maximă permisă (5 MB).';
    } else {
        $filename = basename($file['name']);
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, FORBIDDEN_EXTENSIONS)) {
            $error = 'Extensia .' . htmlspecialchars($ext) . ' este blocată din motive de securitate.';
        } else {
            $userDir = getUserUploadDir($_SESSION['user']);
            $targetPath = $userDir . $filename;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $message = 'Fișier încărcat cu succes!';
            } else {
                $error = 'Eroare la salvarea fișierului pe server.';
            }
        }
    }
}

// Preluare fișiere proprii
$userDir = getUserUploadDir($_SESSION['user']);
$myFiles = [];

if (is_dir($userDir)) {
    $scannedFiles = array_diff(scandir($userDir), ['.', '..']);
    foreach ($scannedFiles as $f) {
        $path = $userDir . $f;
        if (is_file($path)) {
            $myFiles[] = [
                'name' => $f,
                'size' => round(filesize($path) / 1024, 2), // în KB
                'date' => date('d.m.Y H:i', filemtime($path))
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Manager Fișiere</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen">
    <!-- Navbar -->
    <nav class="bg-white border-b border-slate-200 px-6 py-4 flex justify-between items-center">
        <h1 class="text-xl font-bold text-slate-800">Partajare Fișiere</h1>
        <div class="flex items-center gap-4">
            <span class="text-slate-600 text-sm">Utilizator: <strong class="text-slate-800"><?= htmlspecialchars($_SESSION['user']) ?></strong></span>
            <a href="logout.php" class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-medium px-3 py-1.5 rounded-lg transition">Deconectare</a>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto py-8 px-4">
        <!-- Notificări -->
        <?php if ($message): ?>
            <div class="bg-green-50 text-green-700 p-4 rounded-xl mb-6 font-medium border border-green-200"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-50 text-red-700 p-4 rounded-xl mb-6 font-medium border border-red-200"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Formular Upload -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 mb-8">
            <h2 class="text-lg font-semibold text-slate-800 mb-4">Încărcare Fișier Nou</h2>
            <form method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-4">
                <input type="file" name="file" required 
                       class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2 rounded-lg shrink-0 transition">
                    Încarcă
                </button>
            </form>
            <p class="text-xs text-slate-500 mt-2">Dimensiune maximă: 5MB. Scripturile executabile (ex: .php, .exe, .js) sunt restricționate.</p>
        </div>

        <!-- Tabel Fișiere -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-800">Fișierele mele (<?= count($myFiles) ?>)</h2>
            </div>
            
            <?php if (empty($myFiles)): ?>
                <div class="p-8 text-center text-slate-500">
                    Nu ai încărcat niciun fișier încă.
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead class="bg-slate-50 text-slate-700 font-semibold uppercase text-xs">
                            <tr>
                                <th class="px-6 py-3">Nume Fișier</th>
                                <th class="px-6 py-3">Dimensiune</th>
                                <th class="px-6 py-3">Data Încărcării</th>
                                <th class="px-6 py-3 text-right">Acțiuni</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($myFiles as $f): ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4 font-medium text-slate-800"><?= htmlspecialchars($f['name']) ?></td>
                                    <td class="px-6 py-4"><?= $f['size'] ?> KB</td>
                                    <td class="px-6 py-4"><?= $f['date'] ?></td>
                                    <td class="px-6 py-4 text-right space-x-2">
                                        <a href="download.php?file=<?= urlencode($f['name']) ?>" 
                                           class="inline-block bg-emerald-50 text-emerald-600 hover:bg-emerald-100 px-3 py-1 rounded font-medium transition">Descarcă</a>
                                        <form method="POST" action="delete.php" class="inline-block" onsubmit="return confirm('Ești sigur că vrei să ștergi acest fișier?');">
                                            <input type="hidden" name="file" value="<?= htmlspecialchars($f['name']) ?>">
                                            <button type="submit" class="bg-red-50 text-red-600 hover:bg-red-100 px-3 py-1 rounded font-medium transition">Șterge</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>