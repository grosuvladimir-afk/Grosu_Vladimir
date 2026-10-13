<?php
require_once 'config.php';

if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Toate câmpurile sunt obligatorii.';
    } elseif (!preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $username)) {
        $error = 'Numele de utilizator trebuie să aibă între 3 și 20 caractere alfanumerice.';
    } else {
        $users = getUsers();
        if (isset($users[$username])) {
            $error = 'Numele de utilizator este deja utilizat.';
        } else {
            $users[$username] = [
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s')
            ];
            saveUsers($users);
            $success = 'Cont creat cu succes! Te poți autentifica.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Înregistrare - Partajare Fișiere</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-md border border-slate-200">
        <h2 class="text-2xl font-bold text-center text-slate-800 mb-6">Creare Cont Nou</h2>

        <?php if ($error): ?>
            <div class="bg-red-50 text-red-600 p-3 rounded-lg mb-4 text-sm font-medium">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-50 text-green-600 p-3 rounded-lg mb-4 text-sm font-medium">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nume utilizator</label>
                <input type="text" name="username" required 
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Parolă</label>
                <input type="password" name="password" required 
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <button type="submit" 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 rounded-lg transition duration-200">
                Înregistrează-te
            </button>
        </form>

        <p class="text-center text-sm text-slate-600 mt-6">
            Ai deja un cont? 
            <a href="login.php" class="text-blue-600 hover:underline font-medium">Autentifică-te</a>
        </p>
    </div>
</body>
</html>