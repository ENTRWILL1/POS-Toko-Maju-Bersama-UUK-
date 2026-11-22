<?php
require_once __DIR__ . '/app/bootstrap.php';
require_login();
$productCount = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$transactionCount = (int) $pdo->query('SELECT COUNT(*) FROM transactions')->fetchColumn();
$userCount = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$revenue = (float) $pdo->query("SELECT COALESCE(SUM(total_price),0) FROM transactions WHERE status='selesai'")->fetchColumn();
$recent = $pdo->query('SELECT id,transaction_no,date,customer_name,total_price,status FROM transactions ORDER BY date DESC LIMIT 5')->fetchAll();
$pageTitle = 'Dashboard';
include __DIR__ . '/layouts/header.php';
?>
<div class="page-heading"><div><h1>Dashboard</h1><p class="muted">Ringkasan aktivitas Toko Maju Bersama.</p></div><a class="btn" href="/pos-toko-maju/transactions/create.php">+ Transaksi Baru</a></div>
<div class="grid">
    <div class="card"><div class="stat-label">Jumlah Produk</div><div class="stat-value"><?= $productCount ?></div></div>
    <div class="card"><div class="stat-label">Jumlah Transaksi</div><div class="stat-value"><?= $transactionCount ?></div></div>
    <div class="card"><div class="stat-label">Total Pengguna</div><div class="stat-value"><?= $userCount ?></div></div>
    <div class="card"><div class="stat-label">Pendapatan Selesai</div><div class="stat-value" style="font-size:20px"><?= e(rupiah($revenue)) ?></div></div>
</div>
<div class="panel"><div class="page-heading"><h2>Transaksi Terbaru</h2><a class="btn secondary" href="/pos-toko-maju/transactions/index.php">Lihat semua</a></div>
<div class="table-wrap"><table><thead><tr><th>No. Transaksi</th><th>Tanggal</th><th>Pelanggan</th><th>Total</th><th>Status</th></tr></thead><tbody>
<?php if (!$recent): ?><tr><td colspan="5" class="empty">Belum ada transaksi.</td></tr><?php else: foreach ($recent as $row): ?><tr><td><a href="/pos-toko-maju/transactions/view.php?id=<?= (int) $row['id'] ?>"><?= e($row['transaction_no']) ?></a></td><td><?= e($row['date']) ?></td><td><?= e($row['customer_name']) ?></td><td><?= e(rupiah($row['total_price'])) ?></td><td><span class="badge <?= $row['status']==='selesai'?'success':'warning' ?>"><?= e($row['status']) ?></span></td></tr><?php endforeach; endif; ?>
</tbody></table></div></div>
<?php include __DIR__ . '/layouts/footer.php'; ?>
