<?php
require_once __DIR__ . '/../config.php';
if (is_admin()) { header('Location: index.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (($_SESSION['fails'] ?? 0) >= 5 && time() - ($_SESSION['fail_time'] ?? 0) < 300) {
        $error = 'Too many attempts. Try again in a few minutes.';
    } else {
        $st = db()->prepare('SELECT id, password_hash FROM users WHERE username = ?');
        $st->execute([trim((string)($_POST['username'] ?? ''))]);
        $u = $st->fetch();
        if ($u && password_verify((string)($_POST['password'] ?? ''), $u['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int)$u['id'];
            $_SESSION['fails'] = 0;
            header('Location: index.php'); exit;
        }
        $_SESSION['fails'] = ($_SESSION['fails'] ?? 0) + 1;
        $_SESSION['fail_time'] = time();
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login</title><link rel="stylesheet" href="../style.css"><link rel="stylesheet" href="admin.css"></head>
<body><main class="admin-wrap narrow">
<h1>Admin Login</h1>
<?php if ($error): ?><p class="admin-error"><?= e($error) ?></p><?php endif; ?>
<form method="post" class="contact-form"><?= csrf_field() ?>
<label for="username">Username</label><input id="username" name="username" required maxlength="50" autocomplete="username">
<label for="password">Password</label><input id="password" name="password" type="password" required autocomplete="current-password">
<button class="btn" type="submit">Log in</button></form>
</main></body></html>
