<?php
/**
 * delete.php — DELETE: hapus produk
 *  - hanya menerima method POST (konfirmasi dilakukan di index.php lewat JavaScript confirm())
 *  - memakai TRANSACTION: hapus produk + catat log harus sukses KEDUANYA, atau batal semua (BONUS)
 */
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/config/database.php';

// Akses langsung lewat URL (GET) tidak boleh menghapus apa pun
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$pdo = Database::getInstance()->getConnection();
$id  = (int) ($_POST['id'] ?? 0);

try {
    $pdo->beginTransaction();

    // Ambil nama produk dulu untuk dicatat di log (FOR UPDATE = kunci baris selama transaksi)
    $stmt = $pdo->prepare('SELECT id, name FROM products WHERE id = ? FOR UPDATE');
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if (!$product) {
        $pdo->rollBack();
        set_flash('error', 'Produk tidak ditemukan atau sudah dihapus.');
        redirect('index.php');
    }

    // ✅ PREPARED STATEMENT
    $del = $pdo->prepare('DELETE FROM products WHERE id = ?');
    $del->execute([$id]);

    log_activity($pdo, 'delete', $id, $product['name'], 'Produk dihapus');

    $pdo->commit();

    set_flash('success', 'Produk "' . $product['name'] . '" berhasil dihapus.');
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack(); // ada yang gagal -> batalkan SEMUA
    }
    error_log('DB Error (delete): ' . $e->getMessage());
    set_flash('error', 'Gagal menghapus produk. Silakan coba lagi.');
}

redirect('index.php');
