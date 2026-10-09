<?php
/**
 * includes/product_form.php — form dipakai bersama oleh create.php & edit.php
 * Variabel yang dibutuhkan:
 *   $formAction  (string)  URL tujuan form
 *   $submitLabel (string)  teks tombol submit
 *   $form        (array)   nilai field: name, category_id, supplier_id, price, stock
 *   $categories, $suppliers (array of ['id','name'])
 *   $errors      (array)   daftar pesan error validasi
 */
?>
<form method="post" action="<?= e($formAction) ?>" class="card form">
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error" role="alert">
            <strong>Periksa kembali isian Anda:</strong>
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?= e($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="field">
        <label for="name">Nama Produk</label>
        <input type="text" id="name" name="name" maxlength="150" required
               value="<?= e($form['name']) ?>">
    </div>

    <div class="field-row">
        <div class="field">
            <label for="category_id">Kategori</label>
            <select id="category_id" name="category_id" required>
                <option value="">— Pilih kategori —</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= (int) $c['id'] ?>"
                        <?= (int) $form['category_id'] === (int) $c['id'] ? 'selected' : '' ?>>
                        <?= e($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label for="supplier_id">Supplier</label>
            <select id="supplier_id" name="supplier_id" required>
                <option value="">— Pilih supplier —</option>
                <?php foreach ($suppliers as $s): ?>
                    <option value="<?= (int) $s['id'] ?>"
                        <?= (int) $form['supplier_id'] === (int) $s['id'] ? 'selected' : '' ?>>
                        <?= e($s['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="field-row">
        <div class="field">
            <label for="price">Harga (Rp)</label>
            <input type="number" id="price" name="price" min="0" step="0.01" required
                   value="<?= e($form['price']) ?>">
        </div>

        <div class="field">
            <label for="stock">Stok</label>
            <input type="number" id="stock" name="stock" min="0" step="1" required
                   value="<?= e($form['stock']) ?>">
        </div>
    </div>

    <div class="actions">
        <button type="submit" class="btn btn-primary"><?= e($submitLabel) ?></button>
        <a href="index.php" class="btn btn-ghost">Batal</a>
    </div>
</form>
