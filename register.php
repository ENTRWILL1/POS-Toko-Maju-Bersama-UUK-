<?php
require_once __DIR__ . '/app/bootstrap.php';
require_admin();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = post_string('name');
    $email = strtolower(post_string('email'));
    $password = (string) ($_POST['password'] ?? '');
    $role = post_string('role');
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8 || !in_array($role, ['administrator','petugas'], true)) {
        $error = 'Periksa kembali nama, email, role, dan password (minimal 8 karakter).';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,?)');
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
            set_flash('success', 'Pengguna berhasil didaftarkan.');
            redirect('/pos-toko-maju/register.php');
        } catch (PDOException $e) {
            $error = $e->getCode() === '23000' ? 'Email sudah terdaftar.' : 'Gagal menyimpan pengguna.';
        }
    }
}
$pageTitle = 'Registrasi Pengguna';
include __DIR__ . '/layouts/header.php';
?>
<div class="page-heading"><div><h1>Registrasi Pengguna</h1><p class="muted">Khusus administrator untuk membuat akun.</p></div></div>
<div class="panel" style="max-width:650px">
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<div class="field"><label>Nama lengkap</label><input name="name" required></div>
<div class="field"><label>Email</label><input type="email" name="email" required></div>
<div class="field"><label>Password (minimal 8 karakter)</label><input type="password" name="password" minlength="8" required></div>
<div class="field"><label>Role</label><select name="role"><option value="petugas">Petugas</option><option value="administrator">Administrator</option></select></div>
<div class="form-actions"><button class="btn" type="submit">Daftarkan Pengguna</button></div></form></div>
<?php include __DIR__ . '/layouts/footer.php'; ?>
