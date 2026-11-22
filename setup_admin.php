<?php
require_once __DIR__ . '/app/bootstrap.php';
$count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
if ($count > 0) {
    set_flash('error', 'Administrator sudah tersedia. Silakan login.');
    redirect('/pos-toko-maju/login.php');
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = post_string('name');
    $email = strtolower(post_string('email'));
    $password = (string) ($_POST['password'] ?? '');
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        $error = 'Isi nama dan email yang valid. Password minimal 8 karakter.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,'administrator')");
        $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
        set_flash('success', 'Administrator berhasil dibuat. Silakan login.');
        redirect('/pos-toko-maju/login.php');
    }
}
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Buat Administrator</title><link rel="stylesheet" href="/pos-toko-maju/assets/css/style.css"></head>
<body class="login-page"><div class="login-card"><h1>Buat Administrator</h1><p class="muted">Langkah pertama setelah mengimpor database.</p>
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><div class="field"><label>Nama</label><input name="name" required value="<?= e($_POST['name'] ?? '') ?>"></div><div class="field"><label>Email</label><input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>"></div><div class="field"><label>Password (minimal 8 karakter)</label><input type="password" name="password" minlength="8" required></div><button class="btn" type="submit">Buat Administrator</button></form>
<p class="footer-note">Halaman ini hanya bisa digunakan saat tabel users masih kosong. Setelah administrator dibuat, halaman ini otomatis terkunci.</p></div></body></html>
