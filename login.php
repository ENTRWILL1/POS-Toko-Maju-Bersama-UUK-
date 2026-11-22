<?php
require_once __DIR__ . '/app/bootstrap.php';
if (is_logged_in()) redirect('/pos-toko-maju/index.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(post_string('email'));
    $password = (string) ($_POST['password'] ?? '');
    $stmt = $pdo->prepare('SELECT id,name,email,password_hash,role FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        redirect('/pos-toko-maju/index.php');
    }
    $error = 'Email atau password tidak sesuai.';
}
$flash = get_flash();
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Login POS</title><link rel="stylesheet" href="/pos-toko-maju/assets/css/style.css"></head>
<body class="login-page"><div class="login-card"><h1>POS Toko Maju Bersama</h1><p class="muted">Masuk untuk mengelola produk dan transaksi.</p>
<?php if ($flash): ?><div class="alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><div class="field"><label>Email</label><input type="email" name="email" required autofocus value="<?= e($_POST['email'] ?? '') ?>"></div><div class="field"><label>Password</label><input type="password" name="password" required></div><button class="btn" type="submit">Login</button></form>
<p class="footer-note">Pertama kali menjalankan aplikasi? Buka <a href="/pos-toko-maju/setup_admin.php">setup_admin.php</a> untuk membuat akun administrator.</p></div></body></html>
