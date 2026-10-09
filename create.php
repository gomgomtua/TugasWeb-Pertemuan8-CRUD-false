<?php
/**
 * create.php — CREATE: form tambah produk (dropdown kategori & supplier)
 * Alur: Form POST -> validasi -> prepared INSERT (dalam transaction) -> redirect + flash message
 */
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/config/database.php';

$pdo = Database::getInstance()->getConnection();

// Data untuk dropdown (tanpa input user, aman memakai query())
$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$suppliers  = $pdo->query('SELECT id, name FROM suppliers ORDER BY name')->fetchAll();

$errors = [];
$form   = ['name' => '', 'category_id' => 0, 'supplier_id' => 0, 'price' => '', 'stock' => '0'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form   = read_product_input($_POST);
    $errors = validate_product($form);

    if (empty($errors)) {
        $price = (float) $form['price'];
        $stock = (int) $form['stock'];

        try {
            $pdo->beginTransaction();

            // ✅ PREPARED STATEMENT — nilai diikat terpisah dari struktur SQL
            $stmt = $pdo->prepare(
                'INSERT INTO products (name, category_id, supplier_id, price, stock)
                 VALUES (?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $form['name'],
                $form['category_id'],
                $form['supplier_id'],
                $price,
                $stock,
            ]);
            $newId = (int) $pdo->lastInsertId();

            log_activity($pdo, 'create', $newId, $form['name'], 'Produk ditambahkan (stok awal ' . $stock . ')');

            $pdo->commit();

            set_flash('success', 'Produk "' . $form['name'] . '" berhasil ditambahkan.');
            redirect('index.php'); // Post/Redirect/Get
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('DB Error (create): ' . $e->getMessage());
            $errors[] = 'Gagal menyimpan produk. Pastikan kategori dan supplier yang dipilih valid.';
        }
    }
}

$pageTitle   = 'Tambah Produk';
$activeNav   = 'tambah';
$formAction  = 'create.php';
$submitLabel = 'Simpan Produk';
require __DIR__ . '/includes/header.php';
?>

<div class="page-head">
    <div>
        <h1>Tambah Produk</h1>
        <p class="muted">Isi data produk baru, lalu simpan.</p>
    </div>
</div>

<?php require __DIR__ . '/includes/product_form.php'; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
