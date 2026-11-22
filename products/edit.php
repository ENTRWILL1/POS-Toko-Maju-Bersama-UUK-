<?php
require_once __DIR__ . '/../app/bootstrap.php';
require_login();
$id = positive_int($_GET['id'] ?? $_POST['id'] ?? null);
$stmt = $pdo->prepare('SELECT * FROM products WHERE id=?');
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) { set_flash('error', 'Produk tidak ditemukan.'); redirect('/pos-toko-maju/products/index.php'); }
$categories = $pdo->query('SELECT id,category_name FROM categories ORDER BY category_name')->fetchAll();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $categoryId = positive_int($_POST['category_id'] ?? null);
    $name = post_string('product_name');
    $price = filter_var($_POST['price'] ?? null, FILTER_VALIDATE_FLOAT);
    $stock = filter_var($_POST['stock'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    $unit = post_string('unit');
    if (!$categoryId || $name === '' || $price === false || $price < 0 || $stock === false || $unit === '') {
        $error = 'Periksa kembali data produk.';
    } else {
        $update = $pdo->prepare('UPDATE products SET category_id=?,product_name=?,price=?,stock=?,unit=? WHERE id=?');
        $update->execute([$categoryId,$name,$price,$stock,$unit,$id]);
        set_flash('success', 'Produk berhasil diperbarui.');
        redirect('/pos-toko-maju/products/index.php');
    }
}
$value = static fn($key) => $_POST[$key] ?? $product[$key];
$pageTitle = 'Edit Produk';
include __DIR__ . '/../layouts/header.php';
?>
<div class="page-heading"><div><h1>Edit Produk</h1><p class="muted">Perbarui informasi produk.</p></div></div>
<div class="panel" style="max-width:760px"><?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= $id ?>"><div class="field"><label>Kategori</label><select name="category_id" required><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>" <?= (string)$value('category_id') === (string)$category['id'] ? 'selected' : '' ?>><?= e($category['category_name']) ?></option><?php endforeach; ?></select></div><div class="field"><label>Nama produk</label><input name="product_name" required value="<?= e($value('product_name')) ?>"></div><div class="form-grid"><div class="field"><label>Harga</label><input type="number" name="price" min="0" step="0.01" required value="<?= e($value('price')) ?>"></div><div class="field"><label>Stok</label><input type="number" name="stock" min="0" step="1" required value="<?= e($value('stock')) ?>"></div></div><div class="field"><label>Satuan</label><input name="unit" required value="<?= e($value('unit')) ?>"></div><div class="form-actions"><button class="btn" type="submit">Simpan Perubahan</button><a class="btn secondary" href="/pos-toko-maju/products/index.php">Batal</a></div></form></div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
