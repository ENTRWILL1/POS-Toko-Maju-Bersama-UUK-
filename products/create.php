<?php
require_once __DIR__ . '/../app/bootstrap.php';
require_login();
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
        $error = 'Lengkapi semua data dengan benar. Harga tidak boleh negatif dan stok harus bilangan bulat minimal 0.';
    } else {
        $check = $pdo->prepare('SELECT COUNT(*) FROM categories WHERE id=?');
        $check->execute([$categoryId]);
        if (!(int) $check->fetchColumn()) {
            $error = 'Kategori tidak ditemukan.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO products (category_id,product_name,price,stock,unit) VALUES (?,?,?,?,?)');
            $stmt->execute([$categoryId, $name, $price, $stock, $unit]);
            set_flash('success', 'Produk berhasil ditambahkan.');
            redirect('/pos-toko-maju/products/index.php');
        }
    }
}
$pageTitle = 'Tambah Produk';
include __DIR__ . '/../layouts/header.php';
?>
<div class="page-heading"><div><h1>Tambah Produk</h1><p class="muted">Masukkan informasi produk baru.</p></div></div>
<div class="panel" style="max-width:760px"><?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<?php if (!$categories): ?><div class="alert error">Kategori belum tersedia. Periksa database.sql.</div><?php else: ?>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><div class="field"><label>Kategori</label><select name="category_id" required><option value="">Pilih kategori</option><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>" <?= (($_POST['category_id'] ?? '') == $category['id']) ? 'selected' : '' ?>><?= e($category['category_name']) ?></option><?php endforeach; ?></select></div>
<div class="field"><label>Nama produk</label><input name="product_name" required value="<?= e($_POST['product_name'] ?? '') ?>"></div><div class="form-grid"><div class="field"><label>Harga</label><input type="number" name="price" min="0" step="0.01" required value="<?= e($_POST['price'] ?? '') ?>"></div><div class="field"><label>Stok</label><input type="number" name="stock" min="0" step="1" required value="<?= e($_POST['stock'] ?? '0') ?>"></div></div><div class="field"><label>Satuan</label><input name="unit" placeholder="pcs, kg, liter, pack" required value="<?= e($_POST['unit'] ?? '') ?>"></div><div class="form-actions"><button class="btn" type="submit">Simpan Produk</button><a class="btn secondary" href="/pos-toko-maju/products/index.php">Batal</a></div></form><?php endif; ?></div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
