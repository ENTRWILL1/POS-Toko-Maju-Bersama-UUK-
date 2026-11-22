<?php
require_once __DIR__ . '/../app/bootstrap.php';
require_login();
$id = positive_int($_GET['id'] ?? $_POST['id'] ?? null);
$stmt = $pdo->prepare('SELECT * FROM transactions WHERE id=?');
$stmt->execute([$id]);
$trx = $stmt->fetch();
if (!$trx) { set_flash('error', 'Transaksi tidak ditemukan.'); redirect('/pos-toko-maju/transactions/index.php'); }
$products = $pdo->query('SELECT id,product_name,price,stock,unit FROM products ORDER BY product_name')->fetchAll();
$oldStmt = $pdo->prepare('SELECT product_id,quantity FROM transaction_details WHERE transaction_id=? ORDER BY id');
$oldStmt->execute([$id]);
$existingItems = $oldStmt->fetchAll();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $customer = post_string('customer_name');
    $rawItems = $_POST['items'] ?? [];
    $items = [];
    if (is_array($rawItems)) foreach ($rawItems as $item) {
        $pid = positive_int($item['product_id'] ?? null);
        $qty = positive_int($item['quantity'] ?? null);
        if ($pid && $qty) $items[$pid] = ($items[$pid] ?? 0) + $qty;
    }
    if ($customer === '' || !$items) {
        $error = 'Isi nama pelanggan dan minimal satu barang dengan jumlah valid.';
    } else {
        ksort($items);
        try {
            $pdo->beginTransaction();
            $lockTrx = $pdo->prepare('SELECT id FROM transactions WHERE id=? FOR UPDATE');
            $lockTrx->execute([$id]);
            if (!$lockTrx->fetch()) throw new RuntimeException('Transaksi tidak ditemukan.');
            $old = $pdo->prepare('SELECT product_id,quantity FROM transaction_details WHERE transaction_id=?');
            $old->execute([$id]);
            $oldRows = $old->fetchAll();
            $restore = $pdo->prepare('UPDATE products SET stock=stock+? WHERE id=?');
            foreach ($oldRows as $row) $restore->execute([(int)$row['quantity'],(int)$row['product_id']]);
            $pdo->prepare('DELETE FROM transaction_details WHERE transaction_id=?')->execute([$id]);
            $getProduct = $pdo->prepare('SELECT id,price,stock FROM products WHERE id=? FOR UPDATE');
            $updateStock = $pdo->prepare('UPDATE products SET stock=stock-? WHERE id=? AND stock>=?');
            $insertDetail = $pdo->prepare('INSERT INTO transaction_details (transaction_id,product_id,quantity,unit_price,subtotal) VALUES (?,?,?,?,?)');
            $total = 0;
            foreach ($items as $pid=>$qty) {
                $getProduct->execute([$pid]);
                $product = $getProduct->fetch();
                if (!$product) throw new RuntimeException('Salah satu produk tidak ditemukan.');
                if ((int)$product['stock'] < $qty) throw new RuntimeException('Stok produk ID ' . $pid . ' tidak mencukupi.');
                $price=(float)$product['price']; $subtotal=$price*$qty;
                $updateStock->execute([$qty,$pid,$qty]);
                if ($updateStock->rowCount() !== 1) throw new RuntimeException('Gagal memperbarui stok.');
                $insertDetail->execute([$id,$pid,$qty,$price,$subtotal]);
                $total += $subtotal;
            }
            $pdo->prepare('UPDATE transactions SET customer_name=?,total_price=? WHERE id=?')->execute([$customer,$total,$id]);
            $pdo->commit();
            set_flash('success', 'Transaksi berhasil diperbarui. Harga barang dihitung ulang menggunakan harga produk saat ini.');
            redirect('/pos-toko-maju/transactions/view.php?id='.$id);
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = $e instanceof RuntimeException ? $e->getMessage() : 'Gagal memperbarui transaksi. Periksa database.';
        }
    }
}
$initialItems = $_POST['items'] ?? $existingItems;
if (!is_array($initialItems) || !$initialItems) $initialItems=[['product_id'=>'','quantity'=>1]];
$valueCustomer = $_POST['customer_name'] ?? $trx['customer_name'];
$pageTitle = 'Edit Transaksi';
include __DIR__ . '/../layouts/header.php';
?>
<div class="page-heading"><div><h1>Edit Transaksi</h1><p class="muted"><?= e($trx['transaction_no']) ?></p></div></div>
<div class="panel"><?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= $id ?>"><div class="field"><label>Nama pelanggan</label><input name="customer_name" required value="<?= e($valueCustomer) ?>"></div><h2>Barang transaksi</h2><div class="table-wrap"><table><thead><tr><th>Produk</th><th>Jumlah</th></tr></thead><tbody id="transaction-items">
<?php foreach ($initialItems as $i=>$item): ?><tr><td><select name="items[<?= (int)$i ?>][product_id]" required><option value="">Pilih produk</option><?php foreach ($products as $product): ?><option value="<?= (int)$product['id'] ?>" <?= (string)($item['product_id']??'')===(string)$product['id']?'selected':'' ?>><?= e($product['product_name']) ?> — <?= e(rupiah($product['price'])) ?> (stok sekarang <?= (int)$product['stock'] ?>)</option><?php endforeach; ?></select></td><td><input class="qty-input" type="number" min="1" name="items[<?= (int)$i ?>][quantity]" value="<?= e($item['quantity']??1) ?>" required></td></tr><?php endforeach; ?>
</tbody></table></div><div class="form-actions"><button class="btn secondary" type="button" onclick="addEditRow()">+ Tambah Baris</button><button class="btn" type="submit">Simpan Perubahan</button><a class="btn secondary" href="/pos-toko-maju/transactions/view.php?id=<?= $id ?>">Batal</a></div>
<template id="edit-row-template"><tr><td><select name="items[NEW][product_id]" required><option value="">Pilih produk</option><?php foreach ($products as $product): ?><option value="<?= (int)$product['id'] ?>"><?= e($product['product_name']) ?> — <?= e(rupiah($product['price'])) ?></option><?php endforeach; ?></select></td><td><input class="qty-input" type="number" min="1" name="items[NEW][quantity]" value="1" required></td></tr></template></form>
<p class="footer-note">Saat barang atau jumlah diubah, stok lama dikembalikan terlebih dahulu. Harga rincian akan mengikuti harga produk terbaru.</p></div>
<script>let editRowIndex=<?= count($initialItems) ?>;function addEditRow(){const t=document.getElementById('edit-row-template');const c=document.getElementById('transaction-items');const f=t.content.cloneNode(true);f.querySelectorAll('[name]').forEach(el=>el.name=el.name.replaceAll('NEW',String(editRowIndex)));c.appendChild(f);editRowIndex++;}</script>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
