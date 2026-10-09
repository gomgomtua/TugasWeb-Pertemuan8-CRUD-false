<?php
/**
 * edit.php — UPDATE: form edit yang sudah terisi (pre-filled)
 * Alur: GET ?id= -> ambil data lama -> tampilkan form -> POST -> validasi -> prepared UPDATE -> redirect
 */
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/config/database.php';

$pdo = Database::getInstance()->getConnection();

// id dari URL di-cast ke int (lapisan pertahanan tambahan selain prepared statement)
$id = (int) ($_GET['id'] ?? 0);

// Ambil data lama untuk mengisi form
$stmt = $pdo->prepare(
    'SELECT id, name, category_id, supplier_id, price, stock FROM products WHERE id = ?'
);
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    set_flash('error', 'Produk tidak ditemukan.');
    redirect('index.php');
}

$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$suppliers  = $pdo->query('SELECT id, name FROM suppliers ORDER BY name')->fetchAll();

$errors = [];
$form   = $product; // nilai awal form = data lama dari database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form   = read_product_input($_POST);
    $errors = validate_product($form);

    if (empty($errors)) {
        $price = (float) $form['price'];
        $stock = (int) $form['stock'];

        try {
            $pdo->beginTransaction();

            // ✅ PREPARED STATEMENT
            $upd = $pdo->prepare(
                'UPDATE products
                    SET name = ?, category_id = ?, supplier_id = ?, price = ?, stock = ?
                  WHERE id = ?'
            );
            $upd->execute([
                $form['name'],
                $form['category_id'],
                $form['supplier_id'],
                $price,
                $stock,
                $id,
            ]);

            log_activity(
                $pdo,
                'update',
                $id,
                $form['name'],
                'Data diperbarui (stok ' . (int) $product['stock'] . ' → ' . $stock . ')'
            );

            $pdo->commit();

            set_flash('success', 'Produk "' . $form['name'] . '" berhasil diperbarui.');
            redirect('index.php');
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('DB Error (edit): ' . $e->getMessage());
            $errors[] = 'Gagal memperbarui produk. Pastikan kategori dan supplier yang dipilih valid.';
        }
    }
}

$pageTitle   = 'Edit Produk';
$activeNav   = 'produk';
$formAction  = 'edit.php?id=' . $id;
$submitLabel = 'Simpan Perubahan';
require __DIR__ . '/includes/header.php';
?>

<div class="page-head">
    <div>
        <h1>Edit Produk</h1>
        <p class="muted">Ubah data produk #<?= $id ?>, lalu simpan perubahan.</p>
    </div>
</div>

<?php require __DIR__ . '/includes/product_form.php'; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
