<?php
require_once __DIR__ . '/includes/auth.php';

if (admin_logged_in()) {
    redirect('dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Token tidak valid. Refresh halaman lalu coba lagi.';
    } else {
        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($username === ADMIN_USERNAME && password_verify($password, ADMIN_PASSWORD_HASH)) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $username;
            redirect('dashboard.php');
        }

        $error = 'Username atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - BHINEKA.SPACE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body{font-family:Inter,sans-serif}.glass{background:rgba(11,29,51,.74);backdrop-filter:blur(16px);border:1px solid rgba(255,255,255,.1)}</style>
</head>
<body class="min-h-screen bg-[#050D1A] text-white flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,94,19,.18),transparent_45%),radial-gradient(circle_at_bottom_left,rgba(56,189,248,.12),transparent_50%)]"></div>
    <main class="relative z-10 w-full max-w-md glass rounded-3xl p-8 shadow-2xl">
        <a href="../index.php" class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-orange-400 mb-8"><i class="fa-solid fa-arrow-left"></i> Kembali ke Website</a>
        <div class="mb-8">
            <div class="w-14 h-14 rounded-2xl bg-orange-500/15 text-orange-400 flex items-center justify-center mb-4 border border-orange-500/30"><i class="fa-solid fa-lock text-2xl"></i></div>
            <h1 class="text-3xl font-extrabold">Login Admin</h1>
            <p class="text-gray-400 mt-2 text-sm">Masuk untuk mengedit konten website BHINEKA.SPACE.</p>
        </div>

        <?php if ($error): ?>
            <div class="mb-5 rounded-xl border border-red-500/40 bg-red-500/10 text-red-200 px-4 py-3 text-sm"><?= admin_e($error) ?></div>
        <?php endif; ?>

        <form method="post" class="space-y-5">
            <input type="hidden" name="csrf_token" value="<?= admin_e(csrf_token()) ?>">
            <div>
                <label class="block text-sm font-semibold text-gray-300 mb-2">Username</label>
                <input name="username" type="text" required autocomplete="username" class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-white outline-none focus:border-orange-500" placeholder="admin">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-300 mb-2">Password</label>
                <input name="password" type="password" required autocomplete="current-password" class="w-full rounded-xl bg-white/5 border border-white/10 px-4 py-3 text-white outline-none focus:border-orange-500" placeholder="••••••••">
            </div>
            <button class="w-full rounded-xl bg-gradient-to-r from-orange-600 to-orange-400 px-5 py-3 font-bold hover:scale-[1.01] transition-transform"><i class="fa-solid fa-right-to-bracket mr-2"></i>Masuk Dashboard</button>
        </form>

        <div class="mt-6 rounded-xl bg-white/5 border border-white/10 p-4 text-xs text-gray-400 leading-relaxed">
            Default login: <b class="text-white">admin</b> / <b class="text-white">admin123</b>. Setelah upload ke hosting, ganti password di file <b>admin/includes/auth.php</b>.
        </div>
    </main>
</body>
</html>
