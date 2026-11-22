<?php
require_once __DIR__ . '/../app/bootstrap.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/pos-toko-maju/products/index.php');
verify_csrf();
$id = positive_int($_POST['id'] ?? null);
if (!$id) { set_flash('error', 'ID produk tidak valid.'); redirect('/pos-toko-maju/products/index.php'); }
try {
    $stmt = $pdo->prepare('DELETE FROM products WHERE id=?');
    $stmt->execute([$id]);
    set_flash('success', 'Produk berhasil dihapus.');
} catch (PDOException $e) {
    set_flash('error', 'Produk tidak dapat dihapus karena sudah digunakan dalam detail transaksi.');
}
redirect('/pos-toko-maju/products/index.php');
