<?php
require_once __DIR__ . '/../app/bootstrap.php';
require_login();
$products = $pdo->query('SELECT id,product_name,price,stock,unit FROM products ORDER BY product_name')->fetchAll();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $customer = post_string('customer_name');
    $rawItems = $_POST['items'] ?? [];
    $items = [];
    if (is_array($rawItems)) {
        foreach ($rawItems as $item) {
            $pid = positive_int($item['product_id'] ?? null);
            $qty = positive_int($item['quantity'] ?? null);
            if ($pid && $qty) $items[$pid] = ($items[$pid] ?? 0) + $qty;
        }
    }
    if ($customer === '' || !$items) {
        $error = 'Isi nama pelanggan dan tambahkan minimal satu barang dengan jumlah yang valid.';
    } else {
        ksort($items);
        try {
            $pdo->beginTransaction();
            $number = 'TRX-' . date('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(3)));
            $insertTrx = $pdo->prepare('INSERT INTO transactions (transaction_no,customer_name) VALUES (?,?)');
            $insertTrx->execute([$number, $customer]);
            $transactionId = (int) $pdo->lastInsertId();
            $getProduct = $pdo->prepare('SELECT id,price,stock FROM products WHERE id=? FOR UPDATE');
            $updateStock = $pdo->prepare('UPDATE products SET stock=stock-? WHERE id=? AND stock>=?');
            $insertDetail = $pdo->prepare('INSERT INTO transaction_details (transaction_id,product_id,quantity,unit_price,subtotal) VALUES (?,?,?,?,?)');
            $total = 0;
            foreach ($items as $pid => $qty) {
                $getProduct->execute([$pid]);
                $product = $getProduct->fetch();
                if (!$product) throw new RuntimeException('Salah satu produk tidak ditemukan.');
                if ((int)$product['stock'] < $qty) throw new RuntimeException('Stok produk ID ' . $pid . ' tidak mencukupi.');
                $price = (float)$product['price'];
                $subtotal = $price * $qty;
                $updateStock->execute([$qty,$pid,$qty]);
                if ($updateStock->rowCount() !== 1) throw new RuntimeException('Gagal memperbarui stok. Coba lagi.');
                $insertDetail->execute([$transactionId,$pid,$qty,$price,$subtotal]);
                $total += $subtotal;
            }
            $pdo->prepare('UPDATE transactions SET total_price=? WHERE id=?')->execute([$total,$transactionId]);
            $pdo->commit();
            set_flash('success', 'Transaksi berhasil disimpan.');
            redirect('/pos-toko-maju/transactions/view.php?id=' . $transactionId);
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = $e instanceof RuntimeException ? $e->getMessage() : 'Transaksi gagal disimpan. Periksa data dan database.';
        }
    }
}
$pageTitle = 'Transaksi Baru';
include __DIR__ . '/../layouts/header.php';
?>
<div class="page-heading"><div><h1>Transaksi Baru</h1><p class="muted">Masukkan pelanggan dan barang yang dibeli.</p></div></div>
<div class="panel">
<?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<?php if (!$products): ?><div class="alert error">Belum ada produk. Tambahkan produk terlebih dahulu.</div><?php else: ?>
<form method="post" id="transaction-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<div class="field"><label>Nama pelanggan</label><input name="customer_name" required value="<?= e($_POST['customer_name'] ?? '') ?>" placeholder="Nama pelanggan"></div>
<h2>Barang yang dibeli</h2><div class="table-wrap"><table><thead><tr><th>Produk</th><th>Jumlah</th></tr></thead><tbody id="transaction-items">
<?php $oldItems = $_POST['items'] ?? [['product_id'=>'','quantity'=>1]]; if (!is_array($oldItems) || !$oldItems) $oldItems=[['product_id'=>'','quantity'=>1]]; foreach ($oldItems as $i=>$old): ?>
<tr><td><select name="items[<?= (int)$i ?>][product_id]" required><option value="">Pilih produk</option><?php foreach ($products as $product): ?><option value="<?= (int)$product['id'] ?>" <?= (string)($old['product_id']??'')===(string)$product['id']?'selected':'' ?>><?= e($product['product_name']) ?> — <?= e(rupiah($product['price'])) ?> (stok <?= (int)$product['stock'] ?> <?= e($product['unit']) ?>)</option><?php endforeach; ?></select></td><td><input class="qty-input" type="number" min="1" name="items[<?= (int)$i ?>][quantity]" value="<?= e($old['quantity']??1) ?>" required></td></tr>
<?php endforeach; ?></tbody></table></div>
<div class="form-actions"><button class="btn secondary" type="button" onclick="addTransactionRow()">+ Tambah Baris Barang</button><button class="btn" type="submit">Simpan Transaksi</button><a class="btn secondary" href="/pos-toko-maju/transactions/index.php">Batal</a></div>
<template id="transaction-row-template"><tr><td><select name="items[NEW][product_id]" required><option value="">Pilih produk</option><?php foreach ($products as $product): ?><option value="<?= (int)$product['id'] ?>"><?= e($product['product_name']) ?> — <?= e(rupiah($product['price'])) ?> (stok <?= (int)$product['stock'] ?> <?= e($product['unit']) ?>)</option><?php endforeach; ?></select></td><td><input class="qty-input" type="number" min="1" name="items[NEW][quantity]" value="1" required></td></tr></template>
</form>
<?php endif; ?></div>
<script>
let transactionRowIndex = <?= isset($oldItems) ? count($oldItems) : 1 ?>;
function addTransactionRow(){const template=document.getElementById('transaction-row-template');const container=document.getElementById('transaction-items');if(!template||!container)return;const fragment=template.content.cloneNode(true);fragment.querySelectorAll('[name]').forEach(el=>el.name=el.name.replaceAll('NEW',String(transactionRowIndex)));container.appendChild(fragment);transactionRowIndex++;}
</script>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
