<?php
require_once __DIR__ . '/../app/bootstrap.php';
require_login();
$id = positive_int($_GET['id'] ?? null);
$stmt = $pdo->prepare('SELECT * FROM transactions WHERE id=?');
$stmt->execute([$id]);
$trx = $stmt->fetch();
if (!$trx) {
    set_flash('error', 'Transaksi tidak ditemukan.');
    redirect('/pos-toko-maju/transactions/index.php');
}
$detailStmt = $pdo->prepare('SELECT d.*,p.product_name,p.unit FROM transaction_details d JOIN products p ON p.id=d.product_id WHERE d.transaction_id=? ORDER BY d.id');
$detailStmt->execute([$id]);
$details = $detailStmt->fetchAll();
$pageTitle = 'Detail Transaksi';
include __DIR__ . '/../layouts/header.php';
?>
<div class="page-heading">
    <div>
        <h1>Detail Transaksi</h1>
        <p class="muted"><?= e($trx['transaction_no']) ?></p>
    </div>
    <div class="actions"><a class="btn secondary" href="/pos-toko-maju/transactions/edit.php?id=<?= $id ?>">Edit</a><a
            class="btn secondary" href="/pos-toko-maju/transactions/index.php">Kembali</a></div>
</div>
<div class="panel">
    <div class="form-grid">
        <div>
            <div class="muted">Pelanggan</div><strong><?= e($trx['customer_name']) ?></strong>
        </div>
        <div>
            <div class="muted">Tanggal</div><strong><?= e($trx['date']) ?></strong>
        </div>
        <div>
            <div class="muted">Status</div><strong><?= e($trx['status']) ?></strong>
        </div>
        <div>
            <div class="muted">Nomor Transaksi</div><strong><?= e($trx['transaction_no']) ?></strong>
        </div>
    </div>
    <div class="table-wrap" style="margin-top:22px">
        <table>
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($details as $detail): ?>
                    <tr>
                        <td><?= e($detail['product_name']) ?></td>
                        <td><?= e(rupiah($detail['unit_price'])) ?></td>
                        <td><?= (int) $detail['quantity'] ?>     <?= e($detail['unit']) ?></td>
                        <td><?= e(rupiah($detail['subtotal'])) ?></td>
                    </tr><?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="detail-total">Total: <?= e(rupiah($trx['total_price'])) ?></div>
</div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>