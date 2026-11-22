<?php
require_once __DIR__ . '/../app/bootstrap.php';
require_login();
$q = trim((string) ($_GET['q'] ?? ''));
$sql = 'SELECT p.*, c.category_name FROM products p JOIN categories c ON c.id=p.category_id';
$params = [];
if ($q !== '') {
    $sql .= ' WHERE p.product_name LIKE ? OR c.category_name LIKE ?';
    $params = ['%' . $q . '%', '%' . $q . '%'];
}
$sql .= ' ORDER BY p.product_name ASC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
$pageTitle = 'Data Produk';
include __DIR__ . '/../layouts/header.php';
?>
<div class="page-heading"><div><h1>Data Produk</h1><p class="muted">Kelola produk, harga, kategori, dan stok.</p></div><a class="btn" href="/pos-toko-maju/products/create.php">+ Tambah Produk</a></div>
<form class="search-form" method="get"><input type="search" name="q" placeholder="Cari nama produk atau kategori..." value="<?= e($q) ?>"><button class="btn" type="submit">Cari</button><a class="btn secondary" href="/pos-toko-maju/products/index.php">Reset</a></form>
<div class="panel"><div class="table-wrap"><table><thead><tr><th>#</th><th>Nama Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Satuan</th><th>Aksi</th></tr></thead><tbody>
<?php if (!$products): ?><tr><td colspan="7" class="empty">Produk tidak ditemukan.</td></tr><?php else: foreach ($products as $i => $product): ?><tr>
<td><?= $i + 1 ?></td><td><?= e($product['product_name']) ?></td><td><?= e($product['category_name']) ?></td><td><?= e(rupiah($product['price'])) ?></td><td><?= (int) $product['stock'] ?></td><td><?= e($product['unit']) ?></td><td><div class="actions"><a class="btn secondary" href="/pos-toko-maju/products/edit.php?id=<?= (int) $product['id'] ?>">Edit</a><form class="inline-form" method="post" action="/pos-toko-maju/products/delete.php" data-confirm="Hapus produk ini?"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int) $product['id'] ?>"><button class="btn danger" type="submit">Hapus</button></form></div></td>
</tr><?php endforeach; endif; ?>
</tbody></table></div></div>
<?php include __DIR__ . '/../layouts/footer.php'; ?>
