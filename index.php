<?php
/**
 * index.php — READ: daftar produk + nama kategori & supplier (JOIN)
 * Bonus: pencarian produk (?q=...) dengan prepared statement.
 */
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/config/database.php';

$pdo = Database::getInstance()->getConnection();

$q         = trim((string) ($_GET['q'] ?? ''));
$products  = [];
$loadError = null;

try {
    $sql = "SELECT p.id, p.name, p.price, p.stock,
                   c.name AS category,
                   s.name AS supplier
            FROM products p
            JOIN categories c ON p.category_id = c.id
            JOIN suppliers  s ON p.supplier_id = s.id";
    $params = [];

    if ($q !== '') {
        // Input pencarian tetap lewat placeholder (?), BUKAN digabung ke string SQL
        $sql   .= " WHERE p.name LIKE ? OR c.name LIKE ? OR s.name LIKE ?";
        $like   = '%' . $q . '%';
        $params = [$like, $like, $like];
    }

    $sql .= " ORDER BY p.name";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('DB Error (index): ' . $e->getMessage());
    $loadError = 'Terjadi kesalahan database saat mengambil data produk.';
}

$pageTitle = 'Daftar Produk';
$activeNav = 'produk';
require __DIR__ . '/includes/header.php';
?>

<div class="page-head">
    <div>
        <h1>Daftar Produk</h1>
        <p class="muted">
            <?= count($products) ?> produk
            <?= $q !== '' ? 'untuk pencarian “' . e($q) . '”' : 'tercatat' ?>
        </p>
    </div>
    <a href="create.php" class="btn btn-primary">＋ Tambah Produk</a>
</div>

<form method="get" action="index.php" class="search">
    <input type="search" name="q" placeholder="Cari nama produk, kategori, atau supplier…"
           value="<?= e($q) ?>" aria-label="Pencarian produk">
    <button type="submit" class="btn btn-primary">Cari</button>
    <?php if ($q !== ''): ?>
        <a href="index.php" class="btn btn-ghost">Reset</a>
    <?php endif; ?>
</form>

<?php if ($loadError): ?>
    <div class="alert alert-error" role="alert"><?= e($loadError) ?></div>
<?php endif; ?>

<div class="card table-wrap">
    <table>
        <thead>
        <tr>
            <th>No</th>
            <th>Nama Produk</th>
            <th>Kategori</th>
            <th>Supplier</th>
            <th class="num">Harga</th>
            <th class="num">Stok</th>
            <th>Aksi</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($products)): ?>
            <tr>
                <td colspan="7" class="empty">Belum ada data produk.</td>
            </tr>
        <?php endif; ?>

        <?php $no = 1; foreach ($products as $p): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= e($p['name']) ?></td>
                <td><span class="badge"><?= e($p['category']) ?></span></td>
                <td><?= e($p['supplier']) ?></td>
                <td class="num"><?= e(rupiah($p['price'])) ?></td>
                <td class="num">
                    <span class="<?= (int) $p['stock'] < 10 ? 'stock-low' : '' ?>"><?= (int) $p['stock'] ?></span>
                </td>
                <td class="row-actions">
                    <a href="edit.php?id=<?= (int) $p['id'] ?>" class="btn btn-small">Edit</a>

                    <!-- Hapus: method POST + konfirmasi JavaScript -->
                    <form method="post" action="delete.php" class="inline"
                          onsubmit="return confirm(<?= e(json_encode('Hapus produk "' . $p['name'] . '"?')) ?>)">
                        <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                        <button type="submit" class="btn btn-small btn-danger">Hapus</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
