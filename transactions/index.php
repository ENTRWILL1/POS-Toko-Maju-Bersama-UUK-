<?php
require_once __DIR__ . '/../app/bootstrap.php';
require_login();
$q = trim((string) ($_GET['q'] ?? ''));
$sql = 'SELECT * FROM transactions';
$params = [];
if ($q !== '') {
    $sql .= ' WHERE transaction_no LIKE ? OR customer_name LIKE ?';
    $params = ['%' . $q . '%', '%' . $q . '%'];
}
$sql .= ' ORDER BY date DESC, id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll();
$pageTitle = 'Data Transaksi';
include __DIR__ . '/../layouts/header.php';
?>
<div class="page-heading"><div><h1>Data Transaksi</h1><p class="muted">Riwayat penjualan Toko Maju Bersama.</p></div><a class="btn" href="/pos-toko-maju/transactions/create.php">+ Transaksi Baru</a></div>
<form class="search-form" method="get"><input type="search" name="q" placeholder="Cari nomor transaksi atau pelanggan..." value="<?= e($q) ?>"><button class="btn" type="submit">Cari</button><a class="btn secondary" href="/pos-toko-maju/transactions/index.php">Reset</a></form>
<div class="panel"><div class="table-wrap"><table><thead><tr><th>#</th><th>Nomor</th><th>Tanggal</th><th>Pelanggan</th><th>Total</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
<?php if (!$transactions): ?><tr><td colspan="7" class="empty">Transaksi tidak ditemukan.</td></tr><?php else: foreach ($transactions as $i => $trx): ?><tr><td><?= $i+1 ?></td><td><?= e($trx['transaction_no']) ?></td><td><?= e($trx['date']) ?></td><td><?= e($trx['customer_name']) ?></td><td><?= e(rupiah($trx['total_price'])) ?></td><td><span class="badge <?= $trx['status']==='selesai'?'success':'warning' ?>"><?= e($trx['status']) ?></span></td><td><div class="actions"><a class="btn secondary" href="/pos-toko-maju/transactions/view.php?id=<?= (int)$trx['id'] ?>">Detail</a><a class="btn secondary" href="/pos-toko-maju/transactions/edit.php?id=<?= (int)$trx['id'] ?>">Edit</a><form class="inline-form" method="post" action="/pos-toko-maju/transactions/delete.php" data-confirm="Hapus transaksi ini dan kembalikan stok barang?"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$trx['id'] ?>"><button class="btn danger" type="submit">Hapus</button></form></div></td></tr><?php endforeach; endif; ?>
</tbody></table></div></div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
