<?php
require_once __DIR__ . '/../app/bootstrap.php';
require_login();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('/pos-toko-maju/transactions/index.php');
verify_csrf();
$id = positive_int($_POST['id'] ?? null);
if (!$id) { set_flash('error', 'ID transaksi tidak valid.'); redirect('/pos-toko-maju/transactions/index.php'); }
try {
    $pdo->beginTransaction();
    $lock = $pdo->prepare('SELECT id FROM transactions WHERE id=? FOR UPDATE');
    $lock->execute([$id]);
    if (!$lock->fetch()) throw new RuntimeException('Transaksi tidak ditemukan.');
    $details = $pdo->prepare('SELECT product_id,quantity FROM transaction_details WHERE transaction_id=?');
    $details->execute([$id]);
    $restore = $pdo->prepare('UPDATE products SET stock=stock+? WHERE id=?');
    foreach ($details->fetchAll() as $detail) $restore->execute([(int)$detail['quantity'],(int)$detail['product_id']]);
    $delete = $pdo->prepare('DELETE FROM transactions WHERE id=?');
    $delete->execute([$id]);
    $pdo->commit();
    set_flash('success', 'Transaksi dihapus dan stok barang dikembalikan.');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    set_flash('error', $e instanceof RuntimeException ? $e->getMessage() : 'Transaksi gagal dihapus.');
}
redirect('/pos-toko-maju/transactions/index.php');
